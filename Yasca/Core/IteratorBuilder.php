<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Allows composing PHP collections and projections in a functional style
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class IteratorBuilder implements \IteratorAggregate, Wrapper {
	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	private $iterator = null;
	public function getIterator(){
		return $this->iterator;
	}

	public function unwrap(){
		return $this->iterator;
	}

	public function toFunctionPipe(){
		return (new \Yasca\Core\FunctionPipe)
		->wrap($this->unwrap());
	}

	public function from($values){
		if ($this->iterator === null){
			$this->iterator = Iterators::ensureIsIterator($values);

			//TODO: More intelligent handling of RecursiveIterators
			if ($this->iterator instanceof \RecursiveIterator){
				$this->iterator = new \RecursiveIteratorIterator($this->iterator);
			}
		} else {
			throw new IteratorException('Iterator already assigned');
		}
		return $this;
	}

	public function any() {
		return Iterators::any($this->iterator);
	}

	public function choose(callable $projection){
		$this->iterator = Iterators::choose($this->iterator, $projection);
		return $this;
	}

	public function concat() {
		$arguments = \func_get_args();
		\array_unshift($arguments, $this->iterator);
		$this->iterator = Operators::invokeArray([Iterators::_class, 'concat'], $arguments);
		return $this;
	}

	public function contains($value) {
		return Iterators::contains($this->iterator, $value);
	}

	public function count(){
		return Iterators::count($this->iterator);
	}

	public function defaultIfEmpty($value){
		$this->iterator = Iterators::defaultIfEmpty($this->iterator, $value);
		return $this;
	}

	public function elementAt($key){
		return Iterators::elementAt($this->iterator, $key);
	}

	public function elementAtOrNull($key){
		return Iterators::elementAtOrNull($this->iterator, $key);
	}

	public function first() {
		return Iterators::first($this->iterator);
	}

	public function firstOrNull() {
		return Iterators::firstOrNull($this->iterator);
	}

	public static function fold(callable $projection){
		return Iterators::fold($this->iterator, $projection);
	}

	public function forAll(callable $f){
		Iterators::forAll($this->iterator, $f);
	}

	public function groupBy(callable $selector){
		$this->iterator = Iterators::groupBy($this->iterator, $selector);
		return $this;
	}

	public function join($separator){
		return Iterators::join($this->iterator, $separator);
	}

	public function select(callable $projection){
		$this->iterator = Iterators::select($this->iterator, $projection);
		return $this;
	}

	public function selectKeys(callable $projection){
		$this->iterator = Iterators::selectKeys($this->iterator, $projection);
		return $this;
	}

	public function selectMany(callable $manyProjection){
		$this->iterator = Iterators::selectMany($this->iterator, $manyProjection);
		return $this;
	}

	public function slice($offset, $count){
		$this->iterator = Iterators::slice($this->iterator, $offset, $count);
		return $this;
	}

	public function skip($count){
		$this->iterator = Iterators::skip($this->iterator, $count);
		return $this;
	}

	public function take($count){
		$this->iterator = Iterators::take($this->iterator, $count);
		return $this;
	}

	public function toArray($useKeys = false){
		return Iterators::toArray($this->iterator, $useKeys);
	}

	public function toFixedArray($useKeys = false){
		return Iterators::toFixedArray($this->iterator, $useKeys);
	}

	public function toList(){
		return Iterators::toList($this->iterator);
	}

	public function toObjectStorage($keysAsData = false){
		return Iterators::toObjectStorage($this->iterator, $keysAsData);
	}

	public function where(callable $filter){
		$this->iterator = Iterators::where($this->iterator, $filter);
		return $this;
	}

	public function whereRegex($regex, $mode = \RegexIterator::MATCH, $flags = 0, $preg_flags = 0){
		$this->iterator = Iterators::whereRegex($this->iterator, $regex, $mode, $flags, $preg_flags);
		return $this;
	}

	public function unique(){
		$this->iterator = Iterators::unique($this->iterator);
		return $this;
	}

	public function zip(){
		$arguments = \func_get_args();
		\array_unshift($arguments, $this->iterator);
		$this->iterator = Operators::invokeArray([Iterators::_class, 'zip'], $arguments);
		return $this;
	}
}