<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * PHP 5.4.8 does not support calling callable properties as methods
 * This trait allows simulating that support. Remove if/when a future PHP
 * version supports this natively.
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
trait CallablePropertiesAsMethods{
	public function __call($name, array $arguments){
		try{
			$f = $this->$name;
		} catch (\ErrorException $e){
			//Workaround for lack-of-fix for https://bugs.php.net/bug.php?id=51176
			//Calling a function statically from a non-static method on the same class
			//will instead call the function non-statically.
			if (0 === \mb_strpos($e->getMessage(), 'Accessing static property')){
				$f = static::$$name;
			} else {
				throw $e;
			}
		}
		return Operators::invokeArray($f, $arguments);
	}

	public static function __callStatic($name, array $arguments){
		return Operators::invokeArray(static::$$name, $arguments);
	}
}