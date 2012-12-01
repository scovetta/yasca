<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * If the inner iterator has no items, then this iterator will have
 * exactly one item: $defaultValue at $defaultKey
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class DefaultIterator implements \Iterator {
	/** @var \Iterator */ private $innerIterator;
	private $defaultValue;
	private $defaultKey;
	private $innerHasItems = false;
	private $isFirstValidCall = true;

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	public function __construct(\Iterator $iter, $defaultValue, $defaultKey = 0){
		if ($iter instanceof DefaultIterator){
			$this->innerIterator = $iter->innerIterator;
			$this->defaultValue = $iter->defaultValue;
			$this->defaultKey = $iter->defaultKey;
		} else {
			$this->innerIterator = $iter;
			$this->defaultValue = $defaultValue;
			$this->defaultKey = $defaultKey;
		}
	}

	public function current(){
		if ($this->innerHasItems === true){
			return $this->innerIterator->current();
		} else {
			return $this->defaultValue;
		}
	}
	public function key(){
		if ($this->innerHasItems === true){
			return $this->innerIterator->key();
		} else {
			return $this->defaultKey;
		}
	}
	public function next(){
		if ($this->innerHasItems === true){
			$this->innerIterator->next();
		}
	}
	public function rewind(){
		$this->isFirstValidCall = true;
		$this->innerIterator->rewind();
	}
	public function valid(){
		if ($this->isFirstValidCall === true){
			$this->innerHasItems = $this->innerIterator->valid();
			$this->isFirstValidCall = false;
			return true;
		}
		return $this->innerIterator->valid();
	}
}