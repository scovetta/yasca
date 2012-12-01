<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Allows classes to signal that they have
 * resources outside of PHP to be released.
 *
 * Classes relying on PHP reference counting
 * and destructors can find themselves waiting
 * in the 5.3+ garbage collection queue.
 * Using this trait lets the resources go
 * sooner than the PHP process ending or a manual
 * call to \gc_collect_cycles();
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
trait Closeable {
	private $closed = false;

	/**
	 * Can be called multiple times.
	 */
	public function close(){
		if ($this->closed === true){ return; }
		$this->innerClose();
		$this->closed = true;
	}

	/**
	 * Only will be called once.
	 */
	abstract protected function innerClose();

	public function __destruct(){
		$this->close();
	}
}