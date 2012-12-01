<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Provides common interfaces for PHP's multiple collection types.
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class Iterators {
	private function __construct(){}

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;


	/**
	 * Returns true if there are any elements in $values; false otherwise
	 * @param unknown_type $values Any foreach-able object or value
	 */
	public static function any($values){
		foreach($values as $value){
			return true;
		}
		return false;
	}

	/**
	 * Selects projected values that !== null
	 * @throws IteratorException
	 */
	public static function choose(\Iterator $iterator, callable $projection){
		return new \CallbackFilterIterator(
			new ProjectionIterator($iterator, $projection),
			static function($value) { return $value !== null; }
		);
	}

	/**
	 * Concatenates one or more foreach-able object into an Iterator.
	 * If no objects provided, returns an empty iterator.
	 * \AppendIterators provided will be deep copied.
	 * 		This means changes to \AppendIterator parameters will not be reflected
	 * 		in the resulting \Iterator.
	 * @return \Iterator
	 */
	public static function concat(/* $... */){
		$argCount = \func_num_args();
		if ($argCount === 0){
			return new \EmptyIterator();
		} elseif ($argCount === 1){
			return Iterators::ensureIsIterator(\func_get_args()[0]);
		} else {
			$retval = new \AppendIterator();

			//Workaround for AppendIterator bugs
			//https://bugs.php.net/bug.php?id=49104
			//https://bugs.php.net/bug.php?id=62212
			$retval->append(new \ArrayIterator([0]));
			unset($retval->getArrayIterator()[0]);

			$recursiveAttach = static function($iter) use (&$recursiveAttach, $retval){
				foreach($iter as $concatedIter){
					if ($concatedIter instanceof \AppendIterator){
						$recursiveAttach($concatedIter->getArrayIterator());
					} elseif ($concatedIter instanceof \EmptyIterator) {
						//Do not add it.
					} else {
						$retval->append($concatedIter);
					}
				}
				return $retval;
			};
			return (new \Yasca\Core\FunctionPipe)
			->wrap(\func_get_args())
			->pipe([Iterators::_class, 'ensureIsIterator'])
			->pipe([Iterators::_class, 'select'], [Iterators::_class, 'ensureIsIterator'])
			->pipe($recursiveAttach)
			->unwrap();
		}
	}

	/**
	 * Determines if the $values contains $value using strict equality (===).
	 * This will iterate through iterators.
	 * See http://us.php.net/manual/en/function.in-array.php
	 * See http://us.php.net/manual/en/splobjectstorage.contains.php
	 * @param unknown_type $values Any foreach-able object or value
	 * @param unknown_type $value
	 */
	public static function contains($values, $value){
		if (\is_array($values) === true){
			return \in_array($value, $values, true);
		} elseif($values instanceof \SplObjectStorage){
			return $values->contains($value);
		} else {
			foreach($values as $v){
				if ($v === $value){
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Counts the number of items that would show up in a foreach loop
	 * @param unknown_type $values Any foreach-able object or value
	 */
	public static function count($values){
		if (\is_array($values) === true){
			return \count($values);
		} elseif ($values instanceof \Countable){
			return $values->count();
		} elseif ($values instanceof \Iterator){
			return \iterator_count($values);
		} elseif ($values instanceof \IteratorAggregate){
			$iter = $values->getIterator();
			return \iterator_count($iter);
		} else {
			$count = 0;
			foreach($values as $unused){
				$count += 1;
			}
			return $count;
		}
	}

	/**
	 *
	 * If $iterator is empty, return an iterator with $value as the only element.
	 * @param \Iterator $iterator
	 * @param unknown_type $value
	 * @return \Iterator
	 */
	public static function defaultIfEmpty(\Iterator $iterator, $value){
		return new DefaultIterator($iterator, $value);
	}

	/**
	 * Gets the value in the provided $values for the given $key,
     *  or throws IteratorException('Key not found in collection')
	 * Uses strict equality comparison
	 * @param unknown_type $values Any foreach-able object or value
	 * @param unknown_type $key
	 * @return unknown_type|NULL The value, or null if the value is not at that key
	 * @throws IteratorException
	 */
	public static function elementAt($values, $key){
		if (\is_array($values) === true || $values instanceof \ArrayAccess){
			if (isset($values[$key]) === true){
				return $values[$key];
			}
		} elseif ($values === null){
			throw new IteratorException('Collection is null');
		} else {
			foreach($values as $key2 => $value){
				if ($key === $key2){
					return $value;
				}
			}
		}
		throw new IteratorException('Key not found in collection');
	}

	/**
	 * Gets the value in the provided $values for the given $key, or null if that key is not present.
	 * Uses strict equality comparison
	 * @param unknown_type $values Any foreach-able object or value
	 * @param unknown_type $key
	 * @return unknown_type|NULL The value, or null if the value is not at that key
	 */
	public static function elementAtOrNull($values, $key){
		if (\is_array($values) === true || $values instanceof \ArrayAccess){
			if (isset($values[$key]) === true){
				return $values[$key];
			} else {
				return null;
			}
		} elseif ($values === null){
			return null;
		} else {
			foreach($values as $key2 => $value){
				if ($key === $key2){
					return $value;
				}
			}
			return null;
		}
	}

	/**
	 * Wrap the passed in value as necessary to ensure an Iterator is returned.
	 * Rules for $values are, in order:
	 * 		\Iterator => $values
	 * 		Array => \ArrayIterator
	 * 		\IteratorAggregate => the result of $values->getIterator()
	 * 		\Traverseable => \IteratorIterator
	 * 		\Closure => Invoke the closure with no parameters, start over with the result.
	 * 		=== null => \EmptyIterator
	 * 		string => \Iterator of bytes in the string
	 * 		non-string scalar => \Iterator containing one value, $values.
	 * 		Anything else => Copies properties into an array using foreach, wraps in ArrayIterator
	 * @param unknown_type $values
	 */
	public static function ensureIsIterator($values){
		if ($values instanceof \Iterator){
			return $values;
		} elseif (\is_array($values) === true){
			return new \ArrayIterator($values);
		} elseif ($values instanceof \IteratorAggregate){
			return $values->getIterator();
		} elseif ($values instanceof \Traversable){
			if ($values instanceof \DOMNodeList){
				//PHP 5.4.3 IteratorIterator does not behave itself with \DOMNodeList
	        	//https://bugs.php.net/bug.php?id=60762
	        	//As a workaround, cache the items by making an eager copy
		        return self::toList($values);
			} else {
				return new \IteratorIterator($values);
			}
		} elseif ($values instanceof \Closure){
			return self::ensureIsIterator($values());
		} elseif ($values instanceof Wrapper){
			while($values instanceof Wrapper){
				$values = $values->unwrap();
			}
			return self::ensureIsIterator($values);
		} elseif ($values === null){
			return new \EmptyIterator();
		} elseif(\is_scalar($values) === true) {
			if (\is_string($values) === true){
				$len = \strlen($values);
				$arr = new \SplFixedArray();
				for($i = 0; $arr < $len; $i += 1){
					$arr[$i] = $values[$i];
				}
				return $arr;
			} else {
				return new \ArrayIterator([$values]);
			}
		} else {
			$copy = [];
			foreach($values as $key => $value){
				$copy[$key] = $value;
			}
			return new \ArrayIterator($copy);
		}
	}

	/**
	 * Gets the first value, or throws IteratorException('Collection is empty')
	 * @param unknown_type $values Any foreach-able object or value
	 * @return unknown_type The first value
	 * @throws IteratorException
	 */
	public static function first($values){
		foreach($values as $value){
			return $value;
		}
		throw new IteratorException('Collection is empty');
	}

	/**
	 * Gets the first value, or null if there are no values
	 * @param unknown_type $values Any foreach-able object or value
	 * @return unknown_type|NULL The value, or null if there is no first value
	 */
	public static function firstOrNull($values){
		foreach($values as $value){
			return $value;
		}
		return null;
	}

	public static function fold($values, callable $projection){
		$first = true;
		$retval = null;
		foreach($values as $value){
			if ($first === true){
				$retval = $value;
				$first = false;
				continue;
			}
			$retval = $projection($retval, $value);
		}
		return $retval;
	}

	/**
	 * Similar to \iterator_apply and \array_walk,
	 * except the parameters passed to the function
	 * follow the pattern used for \CallbackFilterIterator:
	 * $value, $key, $values.
	 * @param unknown_type $values Any foreachable object or value
	 * @param callable $f ($value, $key, $values)
	 */
	public static function forAll($values, callable $f){
		foreach($values as $key => $value){
			$f($value, $key, $values);
		}
	}

	/**
	 * Group the provided $values, using the result of $selector as the key.
	 * Keys are used as indexes for an array.
	 * @param unknown_type $values Any foreachable object or value
	 * @param callable $selector Params ($value, $key, $values). Returns grouping key.
	 * @return \Iterator of \Iterator
	 */
	public static function groupBy($values, callable $selector){
		$grouping = [];
		foreach($values as $key => $value){
			$groupKey = $selector($value, $key, $values);
			if (isset($grouping[$groupKey]) === false){
				$grouping[$groupKey] = new \SplQueue();
			}
			$grouping[$groupKey]->enqueue($value);
		}
		return new \ArrayIterator($grouping);
	}

	/**
	 * \join(), but for any foreach-able object or value.
	 * See http://php.net/manual/en/function.join.php
	 * @param unknown_type $values Any foreach-able object or value
	 * @param string $separator
	 * @return string
	 */
	public static function join($values, $separator){
		if (\is_array($values) === true){
			return \join($separator, $values);
		}
		$first = true;
		$retval = '';
		foreach($values as $value){
			if ($first === true){
				$retval = "$value";
				$first = false;
				continue;
			}
			$retval = "$retval$separator$value";
		}
		return $retval;
	}

	/**
	 *
	 * Projects items from $iterator to a new iterator.
	 * @param \Iterator $iterator
	 * @param callable $projection
	 */
	public static function select(\Iterator $iterator, callable $projection){
		return new ProjectionIterator($iterator, $projection);
	}

	/**
	 *
	 * Projects items and keys from $iterator to a new iterator.
	 * @param \Iterator $iterator
	 * @param callable $projection
	 */
	public static function selectKeys(\Iterator $iterator, callable $projection){
		return new ProjectionKeyIterator($iterator, $projection);
	}

	/**
	 *
	 * Projects multiple items from each item in $iterator to a new iterator
	 * @param \Iterator $iterator
	 * @param callable $manyProjection
	 */
	public static function selectMany(\Iterator $iterator, callable $manyProjection){
		return new ManyProjectionIterator($iterator, $manyProjection);
	}

	/**
	 *
	 * @param \Iterator $iterator
	 * @param unknown_type $offset
	 * @param unknown_type $count
	 * @return \Iterator
	 */
	public static function slice(\Iterator $iterator, $offset, $count){
		if ($offset - $count === 0){
			return new \EmptyIterator();
		} else {
			return new \LimitIterator($iterator, $offset, $count);
		}
	}

	/**
	 * Skips $count items from iterator
	 * @param \Iterator $iterator
	 * @param unknown_type $count
	 * @return \Iterator
	 */
	public static function skip(\Iterator $iterator, $count){
		if ($count === 0){
			return $iterator;
		} else {
			return new \LimitIterator($iterator, $count);
		}
	}

	/**
	 *
	 * Takes $count items from iterator
	 * @param \Iterator $iterator
	 * @param unknown_type $count
	 * @return \Iterator
	 */
	public static function take(\Iterator $iterator, $count){
		if ($count === 0){
			return new \EmptyIterator();
		} else {
			return new \LimitIterator($iterator, 0, $count);
		}
	}

	/**
	 * Convert $values to an array
	 * @param unknown_type $values Any foreachable object or value.
	 * @param bool $useKeys
	 */
	public static function toArray($values, $useKeys = false){
		if (\is_array($values) === true){
			if ($useKeys === true){
				return $values;
			} else {
				return \array_values($values);
			}
		} elseif ($values instanceof \Iterator){
			return \iterator_to_array($values, $useKeys);
		} elseif ($values instanceof \IteratorAggregate){
			$values = $values->getIterator();
			return \iterator_to_array($values, $useKeys);
		} elseif ($useKeys === true){
			$retval = [];
			foreach($values as $key => $value){
				$retval[$key] = $value;
			}
			return $retval;
		} else {
			$retval = [];
			foreach($values as $value){
				$retval[] = $value;
			}
			return $retval;
		}
	}

	/**
	 * Convert $values to an \SplFixedArray.
	 * @param unknown_type $values Any foreachable object or value.
	 * @param bool $useKeys If used, Keys must be integers.
	 */
	public static function toFixedArray($values, $useKeys = false){
		if (\is_array($values) === true){
			return \SplFixedArray::fromArray($values, $useKeys);
		}
		if ($values instanceof \IteratorAggregate){
			$values = $values->getIterator();
		}
		if ($values instanceof \Countable){
			$fixedArray = new \SplFixedArray($values->count());
			if ($useKeys === true){
				foreach($values as $key => $value){
					$fixedArray[$key] = $value;
				}
			} else {
				$i = 0;
				foreach($values as $value){
					$fixedArray[$i] = $value;
					$i += 1;
				}
			}
			return $fixedArray;
		} else {
			return \SplFixedArray::fromArray(self::toArray($values, $useKeys));
		}
	}

	/**
	 * Copy $values to an \SplDoublyLinkedList.
	 * @param unknown_type $values Any foreachable object or value
	 */
	public static function toList($values){
		$list = new \SplDoublyLinkedList();
		foreach($values as $value){
			$list->push($value);
		}
		return $list;
	}

	/**
	 * Copy $values to an \SplObjectStroage.
	 * @param unknown_type $values Any foreachable object or value
	 * @param bool $keysAsData Attach keys to storage as data
	 */
	public static function toObjectStorage($values, $keysAsData = false){
		$retval = new \SplObjectStorage();
		if ($keysAsData === true){
			foreach($values as $key => $item){
				$retval->attach($item, $key);
			}
		} else {
			foreach($values as $item){
				$retval->attach($item);
			}
		}
		return $retval;
	}

	/**
	 * Iterates over all traits declared by a class or trait,
	 * including traits defined by declared traits and parent classes.
	 * @param unknown_type $classOrTrait
	 * @return \Iterator|\IteratorAggregate
	 */
	public static function traitsOf($classOrTrait){
		$traitsOf = static function($trait) use (&$traitsOf){
			$uses = \class_uses($trait);
			return (new \Yasca\Core\IteratorBuilder)
			->from($uses)
			->concat(
				(new \Yasca\Core\IteratorBuilder)
				->from($uses)
				->selectMany($traitsOf)
			);
		};

		return (new \Yasca\Core\IteratorBuilder)
		->from([$classOrTrait])
		->concat(\class_parents($classOrTrait))
		->selectMany($traitsOf);
	}

	/**
	 *
	 * Create a new iterator by applying a filter
	 * @param \Iterator $iterator
	 * @param callable $filter
	 * @return \Iterator
	 */
	public static function where(\Iterator $iterator, callable $filter){
		return new \CallbackFilterIterator($iterator, $filter);
	}

	/**
	 *
	 * Filters an iterator of strings with a regex expression.
	 * When the regex expression === null, $iterator is returned untouched.
	 * @param \Iterator $iterator
	 * @return \Iterator
	 */
	public static function whereRegex(\Iterator $iterator, $regex, $mode = \RegexIterator::MATCH, $flags = 0, $preg_flags = 0){
		if ($regex === null){
			return $iterator;
		} else {
			return new \RegexIterator($iterator, $regex, $mode, $flags, $preg_flags);
		}
	}

	/**
	 *
	 * Returns an iterator containing unique items from $iterator
	 * @param \Iterator $iterator
	 * @return \Iterator
	 */
	public static function unique(\Iterator $iterator){
		$objectStorage = new \SplObjectStorage();
		return new \CallbackFilterIterator(
			$iterator,
			static function($current) use ($objectStorage){
				static $array = [];
				if (\is_scalar($current) === true){
					if (isset($array[$current]) === false){
						$array[$current] = true;
						return true;
					}
				} else {
					if (isset($objectStorage[$current]) === false){
						$objectStorage[$current] = true;
						return true;
					}
				}
				return false;
			}
		);
	}

	/**
	 * Iterates over each of the objects at once, returning an array of values from objects in argument order.
	 * @return \Iterator
	 */
	public static function zip(/* Foreachable objects... */){
		$retval = new \MultipleIterator(\MultipleIterator::MIT_NEED_ALL|\MultipleIterator::MIT_KEYS_NUMERIC);
		foreach(\func_get_args() as $o){
			$iter = self::ensureIsIterator($o);
			$retval->attachIterator($iter);
		}
		return $retval;
	}
}