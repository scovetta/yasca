<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Provides information about the current environment
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class Environment {
	use CallablePropertiesAsMethods;

	private function __construct(){}

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	/**
	 * Returns the java version available on this machine
	 * 1.X, where X is the version returned.
	 * Returns 0 if there is no Java available.
	 * @return string
	 */
	private static $getJavaVersionAvailable;
	private static $isWindows;
	private static $isLinux;
	private static $isLinuxWithWine;

	/**
	 * Returns if at least the specified java version is available
	 * @param unknown_type $version 1.X, where X is $version.
	 */
	public static function hasAtLeastJavaVersion($version){
		return static::getJavaVersionAvailable() >= $version;
	}
}
\Closure::bind(
	static function(){
		static::$getJavaVersionAvailable =
			Operators::lazy(static function(){
				$matches = [];
				if (\preg_match(
					<<<'EOT'
`(?xm)
	# 1.X, where X is the version we're interested in.
	"	\d+ \. (?<version> \d+ )
`u
EOT
	,				\shell_exec('java -version 2>&1'),
					$matches
				)){
					return \intval($matches['version']);
				} else {
					return 0;
				}
			});

		static::$isWindows =
			Operators::lazy(static function(){
				return (new \Yasca\Core\FunctionPipe)
				->wrap(PHP_OS)
				->pipe('\substr', 0, \strlen('win'))
				->pipe('\strcasecmp', 'win')
				->pipe([Operators::_class, 'equals'], 0)
				->unwrap();
			});

		//TODO: Update with more values where PHP_OS !== 'Linux', but are Linux systems
		static::$isLinux = Operators::identity(PHP_OS === 'Linux');

		static::$isLinuxWithWine =
			Operators::lazy(static function(){
				return static::isLinux() &&
						!preg_match('/no wine in/', \shell_exec('which wine'));
			});
	},
	null,
	__NAMESPACE__ . '\\' . \basename(__FILE__, '.php')
)->__invoke();