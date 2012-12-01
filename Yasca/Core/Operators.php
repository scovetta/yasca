<?
declare(encoding='UTF-8');
namespace Yasca\Core;

final class Operators{
	private function __construct() {}

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	public static function _and($a, $b){
		return $a && $b;
	}

	public static function _or($a, $b){
		return $a || $b;
	}

	public static function call(){
		$arguments = \func_get_args();
		$first = \array_shift($arguments);
		$second = \array_shift($arguments);
		$f = [$first, $second];
		return Operators::invokeArray($f, $arguments);
	}

	public static function callArray($first, $second, array $arguments){
		return Operators::invokeArray([$first, $second], $arguments);
	}

	public static function equals($a, $b){
		return $a === $b;
	}

	public static function notEquals($a, $b){
		return $a !== $b;
	}

	public static function nullCoalesce(){
		foreach(\func_get_args() as $arg){
			if ($arg !== null) { return $arg; }
		}
		return null;
	}

	public static function identity($value) {
		return static function() use ($value) {
			return $value;
		};
	}

	public static function in(){
		$arguments = \func_get_args();
		$value = \array_shift($arguments);
		return \in_array($value, $arguments, true);
	}

	public static function isNullOrEmpty($string){
		return !($string !== null && \is_string($string) === true && $string !== '');
	}

	public static function invoke(){
		$arguments = \func_get_args();
		$f = \array_shift($arguments);
		return self::invokeArray($f, $arguments);
	}

	public static function invokeArray(callable $f, array $arguments){
		$argCount = \count($arguments);
		if ($argCount === 0){
			return $f();
		} elseif ($argCount === 1){
			return $f($arguments[0]);
		} elseif ($argCount === 2){
			return $f($arguments[0], $arguments[1]);
		} elseif ($argCount === 3){
			return $f($arguments[0], $arguments[1], $arguments[2]);
		} else {
			return \call_user_func_array($f, $arguments);
		}
	}

	/**
	 * Compose functions
	 * (inner, outer, arg2, arg3, ...)
	 * Returns a function f, that when called, returns:
	 * outer(inner(), arg2, arg3, ... , f_argument1, f_argument2, ...)
	 *
	 * Enter description here ...
	 */
	public static function compose(){
		$arguments = \func_get_args();
		$innerF = \array_shift($arguments);
		$f = \array_shift($arguments);
		return static function() use ($innerF, $f, $arguments){
			\array_unshift($arguments, $innerF());
			foreach(\func_get_args() as $arg){
				$arguments[] = $arg;
			}
			return Operators::invokeArray($f, $arguments);
		};
	}

	public static function curry(){
		$arguments = \func_get_args();
		$f = \array_shift($arguments);
		if (\count($arguments) === 0){
			return $f;
		}
		return static function() use ($f, $arguments){
			foreach(\func_get_args() as $arg){
				$arguments[] = $arg;
			}
			return Operators::invokeArray($f, $arguments);
		};
	}

	public static function curryTail(){
		$arguments = \func_get_args();
		$f = \array_shift($arguments);
		if (\count($arguments) === 0){
			return $f;
		}
		return static function() use ($f, $arguments){
			$args = \func_get_args();
			foreach($arguments as $arg){
				$args[] = $arg;
			}
			return Operators::invokeArray($f, $args);
		};
	}



	/**
	 * Name of the class is the last argument to the function
	 */
	public static function _new(){
		$arguments = \func_get_args();
		$class = \array_pop($arguments);
		$argCount = \count($arguments);
		if ($argCount === 0){
			return new $class();
		} elseif ($argCount === 1){
			return new $class($arguments[0]);
		} elseif ($argCount === 2){
			return new $class($arguments[0], $arguments[1]);
		} elseif ($argCount === 3){
			return new $class($arguments[0], $arguments[1], $arguments[2]);
		} elseif ($argCount === 4){
			return new $class($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
		} elseif ($argCount === 5){
			return new $class($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
		} else {
			//\call_user_func_array does not work here.
			//Safely build an eval string, where $index can only be an integer.
			return eval(
				'return new $class(' .
				(new \Yasca\Core\IteratorBuilder)
				->from($arguments)
				->select(static function($arg, $index){ return $index; })
				->select(static function($index) { return '$arguments[' . $index . ']'; })
				->join(',') .
				');'
			);
		}
	}

	public static function lazy(callable $valueFactory){
		return static function() use ($valueFactory){
			static $retval = null;
			static $exception = null;
			if (isset($retval) === false){
				try {
					$retval = $valueFactory();
				} catch (\Exception $e){
					$exception = $e;
				}
				unset($valueFactory);
			}
			if (isset($exception) === true){
				throw $exception;
			} else {
				return $retval;
			}
		};
	}

	public static function paramLimit(callable $f, $limit){
		return static function() use ($f, $limit){
			return Operators::invokeArray(
				$f,
				(new \Yasca\Core\IteratorBuilder)
				->from(\func_get_args())
				->take($limit)
				->toArray()
			);
		};
	}

	public static function match(){
		$arguments = \func_get_args();
		$value = \array_shift($arguments);
		foreach($arguments as $list){
			list($condition, $projection) = $list;
			if ($condition($value) === true){
				return $projection($value);
			}
		}
		throw new \Exception('Value not matched');
	}
}