<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\CppCheck;
use \Yasca\Core\Async;
use \Yasca\Core\Environment;
use \Yasca\Core\Process;
use \Yasca\Core\ProcessStartException;

final class Plugin extends \Yasca\Plugin {
	use \Yasca\MulticastPlugin;

	protected function getSupportedFileClasses(){return ['C', 'tpp', 'txx',];}

    public function getResultIterator($path){
    	if (Environment::isWindows() !== true){
    		$this->log(['The copy of CppCheck included with Yasca requires Windows', \Yasca\Logs\Level::ERROR]);
    		return new \EmptyIterator();
    	}
    	try {
    		$process = new Process(
    			'"' . __DIR__ . '/cppcheck" ' .
				'--quiet ' .
				'--enable=all ' .
				'--inline-suppr ' .
				'--xml ' .
				'"' . $path . '"'
			);
    	} catch (ProcessStartException $e){
    		$this->log(['CppCheck failed to start', \Yasca\Logs\Level::ERROR]);
	    	return new \EmptyIterator();
    	}
	    $this->log(['CppCheck launched', \Yasca\Logs\Level::INFO]);

	    return $process->continueWith(function($async){
	    	list($stdout, $stderr) = $async->result();
        	$this->log(['CppCheck completed', \Yasca\Logs\Level::INFO]);
        	$regex = <<<'EOT'
`No C or C\+\+ source files found\.`u
EOT;
	        if (\preg_match($regex, $stderr)){
	        	$this->log(['CppCheck did not find any C or C++ source files', \Yasca\Logs\Level::ERROR]);
		        return Async::fromResult(new \EmptyIterator());
		    }
        	$dom = new \DOMDocument();
        	try {
        		$success = $dom->loadXML($stderr);
        	} catch (\ErrorException $e){
        		$success = false;
        	}
        	if ($success !== true){
        		if ($stderr === '') {
        			$this->log(['CppCheck did not return any data', \Yasca\Logs\Level::ERROR]);
        		} else {
        			$this->log(['CppCheck did not return valid XML', \Yasca\Logs\Level::ERROR]);
	        		$this->log(["CppCheck returned $stderr", \Yasca\Logs\Level::ERROR]);
        		}
		        return Async::fromResult(new \EmptyIterator());
        	}

			return (new \Yasca\Core\IteratorBuilder)
			->from($dom->getElementsByTagName('error'))
			->select(static function($errorNode){
				return (new \Yasca\Result)->setOptions([
        			'pluginName' => 'CppCheck',
        			'category' => "{$errorNode->getAttribute('id')}",
        			'lineNumber' => "{$errorNode->getAttribute('line')}",
        			'filename' => "{$errorNode->getAttribute('file')}",
        			'message' => "{$errorNode->getAttribute('msg')}",
        			'description' => "{$errorNode->getAttribute('msg')}",
        			'references' => [
        				'http://sourceforge.net/projects/cppcheck/' => 'CppCheck Home Page',
        			],
        			'severity' => (new \Yasca\Core\FunctionPipe)
        				->wrap($errorNode->getAttribute('severity'))
        				->pipe(static function($cppcheckSeverity){
			        		//http://cppcheck.sourceforge.net/devinfo/doxyoutput/classSeverity.html
			        		if ($cppcheckSeverity === 'error'){
			        			return 2;
			        		} elseif ($cppcheckSeverity === 'warning'){
			        			return 3;
			        		} elseif ($cppcheckSeverity === 'portability' ||
			        				  $cppcheckSeverity === 'performance' ||
				        			  $cppcheckSeverity === 'style' 	  ||
				        		 	  $cppcheckSeverity === 'portability'
				        	){
				        		return 4;
							} else {
				        			//$cppcheckSeverity === 'debug'
				        			//$cppcheckSeverity === 'information'
				        			//$cppcheckSeverity === 'none'
			        			return 5;
				        	}
						})
						->unwrap(),
        		]);
        	})
        	->where(static function($result){
        		$category = $result->category;
        		if ($category === 'toomanyconfigs' 	||
        			$category === 'syntaxError'		||
        			$category === 'cppcheckError'
        		){
        			return false;
        		} else {
	        		return true;
        		}
        	})
	        ->toFunctionPipe()
	        ->pipe([Async::_class, 'fromResult']);
        });
    }
}