<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\FindBugs;
use \Yasca\Core\Async;
use \Yasca\Core\Environment;
use \Yasca\Core\Process;
use \Yasca\Core\ProcessStartException;
use \Yasca\Core\Operators;

final class Plugin extends \Yasca\Plugin {
	use \Yasca\MulticastPlugin;

	protected function getSupportedFileClasses(){return ['class', 'jar',];}

    public function getResultIterator($path){
    	if (Environment::hasAtLeastJavaVersion(5) !== true){
    		$this->log(['FindBugs requires JRE 1.5 or later.', \Yasca\Logs\Level::ERROR]);
    		return new \EmptyIterator();
    	}
    	try {
    		$process = new Process(
    			'"' . __DIR__ . '/bin/findbugs' .
		    	(Environment::isWindows() ? '.bat' : '') . '"' .
		    	' -home "' . __DIR__ . '" ' .
		    	' -include "' . __DIR__ . '/filter.xml" ' .
		    	'-textui -xml:withMessages -xargs -quiet'
			);
    	} catch (ProcessStartException $e){
    		$this->log(['FindBugs failed to start', \Yasca\Logs\Level::ERROR]);
	    	return new \EmptyIterator();
    	}
	    $this->log(['FindBugs launched', \Yasca\Logs\Level::INFO]);

        (new \Yasca\Core\FunctionPipe)
        ->wrap($path)
        ->pipe([Operators::_class, '_new'], '\RecursiveDirectoryIterator')
        ->toIteratorBuilder()
        ->where(function($fileinfo){
        	return $this->supportsExtension($fileinfo->getExtension());
		})
		->select(static function($fileinfo, $filepath){
			return "$filepath\n";
		})
		->forAll([$process,'writeToStdin']);

        $process->closeStdin();

        return $process->continueWith(function($async) use ($path){
        	list($stdout, $stderr) = $async->result();
        	$this->log(['FindBugs completed', \Yasca\Logs\Level::INFO]);
        	$dom = new \DOMDocument();
        	try {
        		$success = $dom->loadXML($stdout);
        	} catch (\ErrorException $e){
        		$success = false;
        	}
        	if ($success !== true){
        		if ($stdout === ''){
        			$this->log(['FindBugs did not return any data', \Yasca\Logs\Level::ERROR]);
        		} else {
        			$this->log(['FindBugs did not return valid XML', \Yasca\Logs\Level::ERROR]);
        			$this->log(["FindBugs returned $stdout", \Yasca\Logs\Level::ERROR]);
        		}
		        return Async::fromResult(new \EmptyIterator());
        	}

        	$bugPatterns =
        		(new \Yasca\Core\IteratorBuilder)
        		->from($dom->getElementsByTagName('BugPattern'))
        		->selectKeys(static function($patternNode){
        			return [
	        			"{$pattern->getElementsByTagName('Details')->item(0)->nodeValue}",
	        			"{$pattern->getAttribute('type')}"
	        		];
	        	})
	        	->toArray(true);

	        return (new \Yasca\Core\IteratorBuilder)
	        ->from($dom->getElementsByTagName('BugInstance'))
	        ->select(static function($bugInstance) use (&$bugPatterns, $path){
            	$type = $bugInstance->getAttribute('type');
            	$sourceLine = $bugInstance->getElementsByTagName('SourceLine')->item(0);
            	$shortMessage = $bugInstance->getElementsByTagName('ShortMessage')->item(0)->nodeValue;
	        	return (new \Yasca\Result)->setOptions([
					'pluginName' => 'FindBugs',
	        		'severity' => "{$bugInstance->getAttribute('priority')}",
	        		'category' =>
	        			(new \Yasca\Core\FunctionPipe)
	        			->wrap($bugInstance->getAttribute('category'))
	        			->pipeLast('\str_replace', '_', ' ')
	        			->pipe('\strtolower')
	        			->pipe('\ucwords')
	        			->unwrap(),
	        		'lineNumber' => "{$sourceLine->getAttribute('start')}",
	        		'filename' => "$path/{$sourceLine->getAttribute('sourcepath')}",
	        		'references' => [
	        			'http://findbugs.sourceforge.net/bugDescriptions.html#' . \urlencode($type) =>
	        				'FindBugs Bug Description',
	        		],
	        		'message' => "$shortMessage",
	        		'description' => <<<"EOT"
$shortMessage

$bugPatterns[$type]
EOT
,
        		]);
        	})
        	->toFunctionPipe()
        	->pipe([Async::_class,'fromResult']);
        });
    }
}