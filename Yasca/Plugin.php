<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Iterators;
use \Yasca\Core\JSON;
use \Yasca\Core\Operators;

/**
 * Plugin Class
 *
 * This (abstract) class is the parent of all plugin classes.
 * @author Michael V. Scovetta <scovetta@users.sourceforge.net>
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 * @license see doc/LICENSE
 */
abstract class Plugin {
	/**
	 * Contains the installed plugins, sorted by trait:
	 * __NAMESPACE__ . '\MulticastPlugin', and similar traits
	 * defined in this namespace.
	 *
	 * @var array of string => SplFixedArray(pluginName)
	 */
	public static $installedPlugins;

	/**
	 * Gets the supported file classes of the plugin,
	 * where file classes are defined in self::$fileClasses.
	 *
	 * The default implementation uses the last part of the
	 * fully qualified class name as the singular supported
	 * file class. For example, the plugin class
	 * class \Yasca\Plugins\BuiltIn\Injection\SQL\JAVA extends Plugin
	 * will automatically be detected as supporting the file class 'JAVA'
	 * by the below function.
	 */
	protected function getSupportedFileClasses(){
		return
			(new \Yasca\Core\FunctionPipe)
			->wrap(\get_called_class())
			->pipeLast('\explode', '\\')
			->pipe('\array_pop')
			->pipe(static function($fileClass){ return [$fileClass]; })
			->unwrap();
	}

	private static $fileClasses;
	private $types = null;
	public function getSupportedFileTypes(){
		if (isset($this->types) === false){
			$this->types =
				(new \Yasca\Core\IteratorBuilder)
				->from($this->getSupportedFileClasses())
				->selectMany(static function($ext){
					return (new \Yasca\Core\FunctionPipe)
					->wrap(self::$fileClasses)
					->pipe([Iterators::_class,'elementAtOrNull'], $ext)
					->toIteratorBuilder()
					->defaultIfEmpty($ext);
				})
				->toArray();
		}
		return $this->types;
	}

	public function supportsExtension($ext){
		return (new \Yasca\Core\IteratorBuilder)
		->from($this->getSupportedFileTypes())
		->where(static function($supportedExt) use ($ext){
			//PHP 5.4.8 does not have a multibyte-safe case insensitive compare
			return \mb_strlen($supportedExt) === \mb_strlen($ext) &&
				   \mb_stripos($supportedExt, $ext) === 0;
		})
		->any();
	}

	protected final function log($val){
		$f = $this->fireLogEvent;
		$f($val);
	}

	private $fireLogEvent;
	public function __construct(callable $fireLogEvent){
		$this->fireLogEvent = $fireLogEvent;
	}

}
\Closure::bind(
	static function(){
		static::$fileClasses =
			(new \Yasca\Core\FunctionPipe)
			->wrap(__FILE__ . '.FileClasses.json')
			->pipe('\file_get_contents')
			->pipe([JSON::_class, 'decode'], true)
			->unwrap();

		static::$installedPlugins =
			(new \Yasca\Core\IteratorBuilder)
			->from(\get_declared_classes())
			->concat(
				(new \Yasca\Core\IteratorBuilder)
				->from(new \RecursiveDirectoryIterator(
					__DIR__ . '/Plugins',
					\FilesystemIterator::KEY_AS_PATHNAME 		|
					\FilesystemIterator::CURRENT_AS_SELF 		|
					\FilesystemIterator::UNIX_PATHS
				))
				->select(static function($rdi){return $rdi->getSubPathname();})
				->whereRegex('`(?<!base)\.php$`ui')
				->select(static function($relativePath){
					return
						__NAMESPACE__ . '\\Plugins\\' .
						(new \Yasca\Core\FunctionPipe)
						->wrap($relativePath)
						->pipe('\substr', 0, - \strlen('.php'))
						->pipeLast('\str_replace', '/', '\\')
						->unwrap();
				})
			)
			->where(static function($current){
				$c = new \ReflectionClass($current);
				if ($c->isAbstract() !== true && $c->isSubclassOf(__NAMESPACE__ . '\Plugin') === true){
					return true;
				}
				return false;
			})
			->selectKeys(static function($plugin){
				return [
					$plugin,
					(new \Yasca\Core\FunctionPipe)
					->wrap($plugin)
					->pipe([Iterators::_class, 'traitsOf'])
					->toIteratorBuilder()
					->where(static function($trait){
						return $trait === __NAMESPACE__ . '\AggregateFileContentsPlugin' ||
							   $trait === __NAMESPACE__ . '\MulticastPlugin' ||
							   $trait === __NAMESPACE__ . '\SingleFileContentsPlugin' ||
							   $trait === __NAMESPACE__ . '\SingleFilePathPlugin';
					})
					->firstOrNull()
				];
			})
			->where(static function($plugin, $trait){return $trait !== null;})
			->groupBy(static function($plugin, $trait){return $trait;})
			->select(Operators::paramLimit([Iterators::_class,'toFixedArray'], 1))
			->toArray(true);
	},
	null,
	__NAMESPACE__ . '\\' . \basename(__FILE__, '.php')
)->__invoke();