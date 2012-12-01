<?
declare(encoding='UTF-8');

namespace Yasca\Plugins\FxCop;
use \Yasca\Core\Async;
use \Yasca\Core\Environment;
use \Yasca\Core\Iterators;
use \Yasca\Core\Process;
use \Yasca\Core\ProcessStartException;

/**
 * The FxCop plugin is used to find potential vulnerabilities in .NET assemblies.
 * Only the security ruleset is used.
 *
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 * @author Michael Maass 07/02/2009
 * @package Yasca
 */
final class Plugin extends \Yasca\Plugin {
	use \Yasca\MulticastPlugin;

	//@todo Differentiate between .NET exe and dlls and regular exe and dlls.
	protected function getSupportedFileClasses(){return ['exe', 'dll', ];}

	public function getResultIterator($path){
		if ((Environment::isWindows() || Environment::isLinuxWithWine()) !== true){
			$this->log(['FxCop requires Windows or Wine to execute', \Yasca\Logs\Level::ERROR]);
			return new \EmptyIterator();
		}

		$compatibleVersions = [
			'Microsoft Fxcop 10.0',
			'Microsoft Fxcop 1.36',
			'Microsoft Fxcop 1.35',
		];

		$pluginDir =
			(new \Yasca\Core\IteratorBuilder)
			->from([
				__DIR__,
			])
			->concat(
				(new \Yasca\Core\IteratorBuilder)
				->from($compatibleVersions)
				->select(static function($versionDir){
					return __DIR__ . '/' . $versionDir;
				})
			)
			->concat(
				//Check places where Fxcop might be installed on a Windows machine
				(new \Yasca\Core\IteratorBuilder)
				->from([
					'ProgramFiles',
					'ProgramFiles(x86)',
					'ProgramW6432',
				])

				//Windows PHP 5.4.3 segfaults on:
				//Iterators::elementAtOrNull($_ENV, 'ProgramFiles');
				//Pull these out the old fashioned way: test then get.
				->where(static function($specialDir){
					return isset($_ENV[$specialDir]);
				})
				->select(static function($specialDir){
					return $_ENV[$specialDir];
				})

				->selectMany(static function($dir) use ($compatibleVersions){
					return (new \Yasca\Core\IteratorBuilder)
					->from($compatibleVersions)
					->select(static function($versionDir) use ($dir){
						return "$dir/$versionDir";
					});
				})
			)
			->where(static function($dir){
				return (new \SplFileInfo("$dir/FxCopCmd.exe"))->isFile();
			})
			->firstOrNull();

		if ($pluginDir === null){
			$this->log(['FxCop cannot be found. To enable the FxCop plugin, ' .
						'install it from Microsoft in the default location ' .
						' or copy FxCop to the ./Yasca/Plugins/FxCop directory.',
						\Yasca\Logs\Level::ERROR]);
			return new \EmptyIterator();
		}

		try {
    		$process = new Process(
    			(Environment::isLinuxWithWine() === true ? 'wine ' : '') .
				'"' . $pluginDir . '/FxCopCmd.exe" ' .
    			//Skip rather than crash if it reads a file it can't use
				'/ignoreinvalidtargets ' .
				//If when scanning, object references can't be immediately found,
				//check the global assembly cache for it.
				'/searchgac ' .
				'/rule:"' . $pluginDir . '/Rules/SecurityRules.dll" ' .
				'/consolexsl:"' . __DIR__ . '/yasca.xsl" ' .
				'/quiet ' .
				(new \Yasca\Core\IteratorBuilder)
				->from(new \RecursiveDirectoryIterator($path))
				->whereRegex(
					'`(?i)\.(' .
					(new \Yasca\Core\IteratorBuilder)
					->from($this->getSupportedFileTypes())
					->select(static function($element){
						return \preg_quote($element, '`');
					})
					->join('|') .
					')$`u'
				)
				->select(static function($current){
					return " /file:\"$current\"";
				})
				->join('')
			);
    	} catch (ProcessStartException $e){
    		$this->log(['FxCop failed to start', \Yasca\Logs\Level::ERROR]);
	    	return new \EmptyIterator();
    	}
	    $this->log(['FxCop launched', \Yasca\Logs\Level::INFO]);

	    return $process->continueWith(function($async){
	    	list($stdout, $stderr) = $async->result();
        	$this->log(['FxCop completed', \Yasca\Logs\Level::INFO]);
			$dom = new \DOMDocument();
        	try {
	        	$success = $dom->loadXML($stdout);
	        } catch (\ErrorException $e){
	        	$success = false;
	        }
	        if ($success !== true){
	        	if ($stdout === ''){
	        		$this->log(['FxCop did not return any data', \Yasca\Logs\Level::ERROR]);
	        	} else {
	        		$this->log(['FxCop did not return valid XML', \Yasca\Logs\Level::ERROR]);
	        		$this->log(["FxCop returned $stdout", \Yasca\Logs\Level::ERROR]);
	        	}
			    return Async::fromResult(new \EmptyIterator());
	        }
	        return (new \Yasca\Core\IteratorBuilder)
	        ->from($dom->getElementsByTagName('result'))
	        ->select(static function($result){
	        	return (new \Yasca\Result)->setOptions([
					'pluginName' => 'FxCop',
	        	   	'severity' => "{$result->getAttribute('severity')}",
	        		'category' => "{$result->getAttribute('category')}",
	        		'filename' => "{$result->getAttribute('filename')}",
	        		'references' => [
	        			"{$result->getAttribute('reference')}" => 'MSDN',
	        		],
	        		'message' => "{$result->getAttribute('message')}",
	        		'description' => "{$result->getAttribute('description')}",
	        	]);
	        })
	        ->toFunctionPipe()
	        ->pipe([Async::_class, 'fromResult']);
	    });
	}
}