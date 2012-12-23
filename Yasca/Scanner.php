<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Async;
use \Yasca\Core\CallablePropertiesAsMethods;
use \Yasca\Core\Encoding;
use \Yasca\Core\Iterators;
use \Yasca\Core\JSON;
use \Yasca\Core\Operators;
use \Yasca\Core\SplSubjectAdapter;
use \Yasca\Core\Wrapper;

/**
 *
 * This is the main engine behind Yasca. It handles passed options, scanning for target
 * files and plugins, and executing those plugins. The output of this all is a list of
 * Result objects that can be passed to a renderer.
 * @author Michael V. Scovetta <scovetta@users.sourceforge.net>
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 * @license see doc/LICENSE
 * @package Yasca
 */
final class Scanner {
	use CallablePropertiesAsMethods;

	const SECONDS_PER_NOTIFY = 30;
	const VERSION = '3.0.4';

	private static $adjustments;

	public function __construct($options){
		list($subscribeIfCloseable, $closeSubscribedCloseables) =
			Operators::invoke(static function(){
				$closeables = new \SplObjectStorage();
				return [
					static function($object) use ($closeables){
						if (
							(new \Yasca\Core\IteratorBuilder)
							->from(Iterators::traitsOf($object))
							->contains('Yasca\Core\Closeable')
						){
							$closeables->attach($object);
						}
					},
					static function() use ($closeables){
						foreach($closeables as $closeable){
							$closeable->close();
						}
						$closeables->removeAllExcept(new \SplObjectStorage());
					},
				];
			});

		list($fireLogEvent, $fireResultEvent) =
			Operators::invoke(function() use ($subscribeIfCloseable){
				$newEvent = function($name) use ($subscribeIfCloseable){
					$event = new SplSubjectAdapter();
					$this->{"attach{$name}Observer"} = function(\SplObserver $observer) use ($event, $subscribeIfCloseable){
						$event->attach($observer);
						$subscribeIfCloseable($observer);
						return $this;
					};
					$this->{"detach{$name}Observer"} = function(\SplObserver $observer) use ($event, $subscribeIfCloseable){
						$event->detach($observer);
						$subscribeIfCloseable($observer);
						return $this;
					};
					return static function($value) use ($event){
						$event->raise($value);
					};
				};
				return [
					$newEvent('Log'),
					$newEvent('Result'),
				];
			});

		$targetDirectory =
			(new \Yasca\Core\FunctionPipe)
			->wrap($options)
			->pipe([Iterators::_class,'elementAtOrNull'], 'targetDirectory')
			->pipe('\realpath')
			->unwrap();

		$makeRelative =
			//Make filenames relative when publishing a result
			(new \Yasca\Core\FunctionPipe)
			->wrap($targetDirectory)
			->pipe('\preg_quote', '`')
			->pipe(static function($dirLiteral) { return "`^$dirLiteral`ui"; })
			->pipe(static function($regex){
				return Operators::curry('\preg_replace', $regex, '');
			})
			->unwrap();

		//Wrap Result event trigger to make changes to each Result
		$fireResultEvent = static function(Result $result) use ($fireResultEvent, $makeRelative){
			//Make adjustments based on adjustments data
			(new \Yasca\Core\FunctionPipe)
			->wrap(static::$adjustments)
			->pipe([Iterators::_class, 'elementAtOrNull'], $result->pluginName)
			->pipe([Iterators::_class, 'elementAtOrNull'], $result->category)
			->pipe(static function($options) use ($result){
				if ($options !== null){
					$result->setOptions($options);
				}
			});

			//Get unsafeSourceCode if needed, and then make the filename relative
			//to the scan directory
			if (isset($result->filename) === true && !Operators::isNullOrEmpty($result->filename)){
				if(isset($result->lineNumber) === true && isset($result->unsafeSourceCode) !== true){
					try {
						$result->unsafeSourceCode =
							(new \Yasca\Core\FunctionPipe)
							->wrap($result->filename)
							->pipe([Encoding::_class,'getFileContentsAsArray'])
							->toIteratorBuilder()
							->slice(
								\max($result->lineNumber - 10, 0),
								20
							)
							->toArray(true);
					} catch (\ErrorException $e){
						$tail = 'No such file or directory';
						if (\substr($e->getMessage(),0-strlen($tail)) === $tail){
							//External tool generated a filename that's not present
							//FindBugs can often do this if the matching .java files are missing.
						} else {
							throw $e;
						}
					}
				}
				$result->setOptions([
					'filename' => "{$makeRelative($result->filename)}",
				]);
			}
			$fireResultEvent($result);
		};

		$createPlugins =
			Operators::curry(
				static function($ignoreRegex, $onlyRegex) use ($fireLogEvent){
					$retval =
						(new \Yasca\Core\IteratorBuilder)
						->from(Plugin::$installedPlugins)
						->select(static function($plugins) use ($ignoreRegex, $onlyRegex, $fireLogEvent){
							return (new \Yasca\Core\IteratorBuilder)
							->from($plugins)
							->whereRegex($ignoreRegex)
							->whereRegex($onlyRegex)
							->select(static function($pluginName) use ($fireLogEvent){
								$p = new $pluginName($fireLogEvent);
								$fireLogEvent(["Plugin $pluginName Loaded", \Yasca\Logs\Level::DEBUG]);
								return $p;
							})
							->toObjectStorage();
						})
						->where([Iterators::_class,'any'])
						->toArray(true);
					$fireLogEvent(['Selected Plugins Loaded', \Yasca\Logs\Level::DEBUG]);
					return $retval;
				},
				(new \Yasca\Core\FunctionPipe)
				->wrap($options)
				->pipe([Iterators::_class, 'elementAtOrNull'], 'pluginsIgnore')
				->toIteratorBuilder()
				->select(static function($literal){return \preg_quote($literal, '`');})
				->toFunctionPipe()
				->pipe([Iterators::_class, 'join'], '|')
				->pipe(static function($string){
					if (Operators::isNullOrEmpty($string) === true){
						return null;
					} else {
						return "`^(?!.*($string).*$)`u";
					}
				})
				->unwrap(),
				(new \Yasca\Core\FunctionPipe)
				->wrap($options)
				->pipe([Iterators::_class, 'elementAtOrNull'], 'pluginsOnly')
				->toIteratorBuilder()
				->select(static function($literal){return \preg_quote($literal, '`');})
				->toFunctionPipe()
				->pipe([Iterators::_class, 'join'], '|')
				->pipe(static function($string){
					if (Operators::isNullOrEmpty($string) === true){
						return null;
					} else {
						return "`($string)`u";
					}
				})
				->unwrap()
			);

		$createTargetIterator =
			Operators::curry(
				static function($extensionRegex, $extensionsIgnoreRegex, $extensionsOnlyRegex, $pluginArray) use ($targetDirectory){
					//Only select files that plugins ask for
					return (new \Yasca\Core\IteratorBuilder)
					->from(new \RecursiveDirectoryIterator(
						$targetDirectory,
						\FilesystemIterator::KEY_AS_PATHNAME 	 |
						\FilesystemIterator::CURRENT_AS_FILEINFO |
						\FilesystemIterator::UNIX_PATHS
					))
					->whereRegex($extensionRegex($pluginArray), \RegexIterator::MATCH, \RegexIterator::USE_KEY)
					->whereRegex($extensionsIgnoreRegex, \RegexIterator::MATCH, \RegexIterator::USE_KEY)
					->whereRegex($extensionsOnlyRegex, \RegexIterator::MATCH, \RegexIterator::USE_KEY)
					;
				},
				static function($pluginArray){
					return (new \Yasca\Core\IteratorBuilder)
					->from($pluginArray)
					->selectMany(static function($plugins){
						return (new \Yasca\Core\IteratorBuilder)
						->from($plugins);
					})
					->selectMany(static function($plugin){
						return (new \Yasca\Core\IteratorBuilder)
						->from($plugin->getSupportedFileTypes());
					})
					->unique()
					->select(static function($ext){
						return \preg_quote($ext, '`');
					})
					->toFunctionPipe()
					->pipe([Iterators::_class, 'join'], '|')
					->pipe(static function($string){
						if (Operators::isNullOrEmpty($string) === true){
							return null;
						} else {
							return "`\.($string)$`ui";
						}
					})
					->unwrap();
				},
				(new \Yasca\Core\FunctionPipe)
				->wrap($options)
				->pipe([Iterators::_class, 'elementAtOrNull'], 'extensionsIgnore')
				->toIteratorBuilder()
				->select(static function($ext){return '.' . \trim($ext, '.');})
				->select(static function($literal){return \preg_quote($literal, '`');})
				->toFunctionPipe()
				->pipe([Iterators::_class, 'join'], '|')
				->pipe(static function($string){
					if (Operators::isNullOrEmpty($string) === true){
						return null;
					} else {
						return "`(?<!$string)$`ui";
					}
				})
				->unwrap(),
				(new \Yasca\Core\FunctionPipe)
				->wrap($options)
				->pipe([Iterators::_class, 'elementAtOrNull'], 'extensionsOnly')
				->toIteratorBuilder()
				->select(static function($ext){return '.' . \trim($ext, '.');})
				->select(static function($literal){return \preg_quote($literal, '`');})
				->toFunctionPipe()
				->pipe([Iterators::_class, 'join'], '|')
				->pipe(static function($string){
					if (Operators::isNullOrEmpty($string) === true){
						return null;
					} else {
						return "`($string)$`ui";
					}
				})
				->unwrap()
			);

		$debug =
			(new \Yasca\Core\FunctionPipe)
			->wrap($options)
			->pipe([Iterators::_class,'elementAtOrNull'], 'debug')
			->pipe([Operators::_class,'equals'], true)
			->unwrap();

		$processResults =
			static function($results) use ($fireResultEvent, &$processResults){
				if ($results instanceof Result){
					$fireResultEvent($results);
					return new \EmptyIterator();
				} elseif ($results instanceof Async){
					if ($results->isDone() === true){
						return $processResults($results->result());
					} else {
						return Iterators::ensureIsIterator([$results]);
					}
				} elseif ($results instanceof Wrapper){
					return $processResults($results->unwrap());
				} else {
					return (new \Yasca\Core\IteratorBuilder)
					->from($results)
					->selectMany($processResults);
				}
			};

		$this->executeAsync = static function() use (
			$fireLogEvent,
			$processResults,
			$closeSubscribedCloseables, $debug,
			$makeRelative, $createPlugins,
			$targetDirectory, $createTargetIterator
		){
			try {
				$fireLogEvent(['Yasca ' . Scanner::VERSION . ' - http://www.yasca.org/ - Michael V. Scovetta', \Yasca\Logs\Level::INFO]);
				$fireLogEvent(["Scanning $targetDirectory", \Yasca\Logs\Level::INFO]);


				$plugins = $createPlugins();

				$multicasts = Iterators::elementAtOrNull($plugins, __NAMESPACE__ . '\MulticastPlugin');

				$lastStatusReportedTime = \time();
				$filesProcessed = 0;
				$awaits = [];
				foreach($createTargetIterator($plugins) as $filePath => $targetFileInfo){
					$fireLogEvent(["Checking file {$makeRelative($filePath)}", \Yasca\Logs\Level::DEBUG]);

					$n = \time();
					if ($n - $lastStatusReportedTime > self::SECONDS_PER_NOTIFY){
						$fireLogEvent(["$filesProcessed files scanned", \Yasca\Logs\Level::INFO]);
						$lastStatusReportedTime = $n;
					}

					$ext = $targetFileInfo->getExtension();
					$getFileContents =
						(new \Yasca\Core\FunctionPipe)
						->wrap($filePath)
						->pipeLast([Operators::_class,'curry'], [Encoding::_class, 'getFileContentsAsArray'])
						->pipe([Operators::_class,'lazy'])
						->unwrap();

					$awaits =
						(new \Yasca\Core\IteratorBuilder)
						->from($awaits)
						->concat(
							//Multicast plugin results
							(new \Yasca\Core\IteratorBuilder)
							->from($multicasts)

							//Make a copy to allow removing elements iterated over
							->toFunctionPipe()
							->pipe([Iterators::_class,'toList'])
							->toIteratorBuilder()

							->where(static function($plugin) use ($ext){
								return $plugin->supportsExtension($ext);
							})
							->select(static function($plugin) use ($multicasts, $targetDirectory){
								$multicasts->detach($plugin);
								return $plugin->getResultIterator($targetDirectory);
							}),

							//Single file path plugin results
							(new \Yasca\Core\FunctionPipe)
							->wrap($plugins)
							->pipe([Iterators::_class, 'elementAtOrNull'], __NAMESPACE__ . '\SingleFilePathPlugin')
							->toIteratorBuilder()
							->where(static function($plugin) use ($ext){
								return $plugin->supportsExtension($ext);
							})
							->select(static function($plugin) use ($filePath){
								return $plugin->getResultIterator($filePath);
							}),

							//Single file contents plugin results
							(new \Yasca\Core\FunctionPipe)
							->wrap($plugins)
							->pipe([Iterators::_class, 'elementAtOrNull'], __NAMESPACE__ . '\SingleFileContentsPlugin')
							->toIteratorBuilder()
							->where(static function($plugin) use ($ext){
								return $plugin->supportsExtension($ext);
							})
							->select(static function($plugin) use ($getFileContents, $filePath){
								return $plugin->getResultIterator($getFileContents(), $filePath);
							})
						)
						->selectMany($processResults)
						->toList();

					(new \Yasca\Core\FunctionPipe)
					->wrap($plugins)
					->pipe([Iterators::_class, 'elementAtOrNull'], __NAMESPACE__ . '\AggregateFileContentsPlugin')
					->toIteratorBuilder()
					->where(static function($plugin) use ($ext){
						return $plugin->supportsExtension($ext);
					})
					->forAll(static function($plugin) use ($getFileContents, $filePath){
						$plugin->apply($getFileContents(), $filePath);
					});

					Async::tickAll();
					$filesProcessed += 1;
				}
				$fireLogEvent(['Finished with files. Gathering results from Aggregate plugins', \Yasca\Logs\Level::DEBUG]);

				$awaits =
					(new \Yasca\Core\FunctionPipe)
					->wrap($plugins)
					->pipe([Iterators::_class, 'elementAtOrNull'], __NAMESPACE__ . '\AggregateFileContentsPlugin')
					->toIteratorBuilder()
					->select(static function($plugin){return $plugin->getResultIterator();})
					->concat($awaits)
					->selectMany($processResults)
					->toList();
				if (Iterators::any($awaits) === true){
					$fireLogEvent(['Waiting on external plugins', \Yasca\Logs\Level::INFO]);
					return (new Async(
						static function() use (
							&$awaits, $processResults, $fireLogEvent
						){
							$awaits =
								(new \Yasca\Core\IteratorBuilder)
								->from($awaits)
								->selectMany($processResults)
								->toList();
							return Iterators::any($awaits) === false;
						},
						static function() use ($fireLogEvent) {
							$fireLogEvent(['Scan complete', \Yasca\Logs\Level::INFO]);
							return null;
						},
						static function(\Exception $exception) use ($fireLogEvent, $debug){
							$fireLogEvent(['Scan aborted', \Yasca\Logs\Level::ERROR]);
							if ($debug === true){
								throw $exception;
							} else {
								$fireLogEvent([$exception->getMessage(), \Yasca\Logs\Level::ERROR]);
								return null;
							}
						}
					))
					->whenDone($closeSubscribedCloseables);
				} else {
					$fireLogEvent(['Scan complete', \Yasca\Logs\Level::INFO]);
					return Async::fromResult(null)->whenDone($closeSubscribedCloseables);
				}
			} catch (\Exception $exception){
				$fireLogEvent(['Scan aborted', \Yasca\Logs\Level::ERROR]);
				if ($debug === true) {
					$closeSubscribedCloseables();
					throw $exception;
				} else {
					$fireLogEvent([$exception->getMessage(), \Yasca\Logs\Level::ERROR]);
					return Async::fromResult(null)->whenDone($closeSubscribedCloseables);
				}
			}
		};

		$this->execute = function(){
			$f = $this->executeAsync;
			return $f()->result();
		};
	}
}
\Closure::bind(
	static function(){
		static::$adjustments =
			(new \Yasca\Core\FunctionPipe)
			->wrap(__FILE__ . '.adjustments.json')
			->pipe('\file_get_contents')
			->pipe([JSON::_class,'decode'], true)
			->unwrap();
	},
	null,
	__NAMESPACE__ . '\\' . \basename(__FILE__, '.php')
)->__invoke();