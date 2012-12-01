<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Projects the values from an iterator using a callable.
 * Values returned in array or Traversable format are unrolled.
 * A lazy evaluated version of \array_map
 * See http://php.net/manual/en/function.array-map.php
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class ManyProjectionIterator implements \Iterator {
	/** @var \Iterator */ private $innerIterator;
	/** @var \Iterator */ private $currentIterator;
	/** @var callable */  private $projection;
	/** @var bool */ 	  private $projectionNeeded = true;

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	/**
	 * @param \Iterator $iter
	 * @param callable $projection Params: (value, key, iterator). Returns Iterator of newValues
	 */
	public function __construct(\Iterator $iter, callable $projection){
		$this->innerIterator = $iter;
		$this->projection = $projection;
	}

	private function project(){
		$projection = $this->projection;
		$this->currentIterator =
			$projection(
				$this->innerIterator->current(),
				$this->innerIterator->key(),
				$this->innerIterator
			);
		if ($this->currentIterator instanceof \IteratorAggregate){
			$this->currentIterator = $this->currentIterator->getIterator();
		} elseif (($this->currentIterator instanceof \Iterator) !== true){
			throw new \BadMethodCallException('Projection did not return an iterator');
		}
		$this->projectionNeeded = false;
	}

	public function current(){return $this->currentIterator->current();}
	public function key(){return $this->currentIterator->key();}
	public function next(){ $this->currentIterator->next();}
	public function rewind(){
		$this->projectionNeeded = true;
		unset($currentIterator);
		$this->innerIterator->rewind();
	}
	public function valid(){
		while($this->innerIterator->valid() === true){
			if ($this->projectionNeeded === true){
				$this->project();
				$this->currentIterator->rewind();
			}
			if ($this->currentIterator->valid() === true){
				return true;
			} else {
				$this->innerIterator->next();
				$this->projectionNeeded = true;
			}
		}
		return false;
	}
}