<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Defines a standard way to set options on a class.
 */
trait Options {
	/**
	 * @param $options Any foreach-able object or value
	 */
	public function setOptions($options){
		foreach ($options as $key => $value){
			$this->setOption($key, $value);
		}
		return $this;
	}
	abstract protected function setOption($key, $value);
}