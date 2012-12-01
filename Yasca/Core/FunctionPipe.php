<?
declare(encoding='UTF-8');
namespace Yasca\Core;

final class FunctionPipe implements Wrapper{
	public function __construct(){}
	private $value = null;

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	public function wrap($value){
		$this->value = $value;
		return $this;
	}

	public function unwrap(){
		return $this->value;
	}

	public function toIteratorBuilder(){
		return (new \Yasca\Core\IteratorBuilder)
		->from($this->unwrap());
	}

	public function pipe(){
		$arguments = \func_get_args();
		$f = $arguments[0];
		$arguments[0] = $this->value;
		$this->value = Operators::invokeArray($f, $arguments);
		return $this;
	}

	public function pipeLast(){
		$arguments = \func_get_args();
		$f = \array_shift($arguments);
		$arguments[] = $this->value;
		$this->value = Operators::invokeArray($f, $arguments);
		return $this;
	}
}