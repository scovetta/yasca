<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\Pixy;
use \Yasca\Core\Async;
use \Yasca\Core\Environment;
use \Yasca\Core\Iterators;
use \Yasca\Core\Process;
use \Yasca\Core\ProcessStartException;

/**
 * The Pixy Plugin uses Pixy to discover potential vulnerabilities in PHP 4 files.
 * Uses Pixy 3.0
 * @package Yasca
 */
final class Plugin extends \Yasca\Plugin {
	use \Yasca\SingleFilePathPlugin,
		\Yasca\Plugins\BuiltIn\Injection\XSS\Base,
		\Yasca\Plugins\BuiltIn\Injection\SQL\Base
	{
			\Yasca\Plugins\BuiltIn\Injection\XSS\Base::newResult insteadof \Yasca\Plugins\BuiltIn\Injection\SQL\Base;
			\Yasca\Plugins\BuiltIn\Injection\XSS\Base::newResult as newXssResult;
			\Yasca\Plugins\BuiltIn\Injection\SQL\Base::newResult as newSqliResult;
	}

    protected function getSupportedFileClasses(){return ['PHP', ];}

    private $firstRun = true;
    public function getResultIterator($filepath){
    	if (Environment::hasAtLeastJavaVersion(6) !== true){
    		if ($this->firstRun === true){
    			$this->log(['Pixy requires JRE 1.6 or later.', \Yasca\Logs\Level::ERROR]);
    		}
    		$this->firstRun = false;
    		return new \EmptyIterator();
    	}

    	try {
    		$process = new Process(
    			'"' . __DIR__ . '/pixy' .
				(Environment::isWindows() ? '.bat' : '') . '"' .
				//TODO: Proper shell escaping for all platforms
				//\escapeshellarg does not function properly on windows
				' "' . $filepath . '"'
			);
    	} catch (ProcessStartException $e){
    		if ($this->firstRun === true){
	    		$this->log(['Unable to start Pixy', \Yasca\Logs\Level::ERROR]);
    		}
	    	$this->firstRun = false;
	    	return new \EmptyIterator();
    	}
	    $this->log(['Pixy launched', \Yasca\Logs\Level::DEBUG]);

    	return $process->continueWith(function($async){
    		list($stdout, $stderr) = $async->result();
        	$this->log(['Pixy completed', \Yasca\Logs\Level::DEBUG]);

    		$matches = [];
    		\preg_match_all(
    			<<<'EOT'
`(*ANY)(?xim)
	^ (?<analysis>
		XSS		|
		SQL 	|
		File
	)
	\s Analysis \s BEGIN \R

	(^ .* (?<! Analysis \s BEGIN | \d ) \R)*

	(?<results>
		(^ 	-	\d*	.* \: \d+ $ \R?)+
	)
`u
EOT
, 				$stdout, $matches, PREG_SET_ORDER
			);
    		return (new \Yasca\Core\IteratorBuilder)
    		->from($matches)
    		->selectKeys(static function($analysisMatch){
    			return [$analysisMatch['results'], $analysisMatch['analysis']];
        	})
        	->whereRegex(
        		<<<'EOT'
`(*ANY)(?xim)
	^ - \d* (?<filename> .* ) : (?<lineNumber> \d+ ) $.
`u
EOT
, 				\RegexIterator::ALL_MATCHES, 0, PREG_SET_ORDER
			)
        	->selectMany(function($results, $analysis){
        		if ($analysis === 'XSS'){
        			$result = $this->newXssResult();
        		} elseif ($analysis === 'SQL'){
        			$result = $this->newSqliResult();
        		} else /*if ($analysis === 'File')*/ {
        			$result = (new \Yasca\Result)->setOptions([
        				'description' => 'Generic File Vulnerability',
        			]);
        		}
        		return (new \Yasca\Core\IteratorBuilder)
        		->from($results)
        		->select(static function($match) use ($result){
        			$r = clone $result;
        			return $r->setOptions([
        				'pluginName' => 'Pixy',
        				'lineNumber' => "{$match['lineNumber']}",
        				'filename' => "{$match['filename']}",
        				'message' => "{$match['filename']}",
        			]);
        		});
        	})
        	->toFunctionPipe()
        	->pipe([Async::_class,'fromResult']);
    	});
    }
}