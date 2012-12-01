<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Simulates an event handler with a value.
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class SplObserverAdapter implements \SplObserver {
	/** @val callable */ private $listener;

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	/**
	 * @param callable $listener Params: (value from event). Return value ignored.
	 */
	public function __construct(callable $listener){
		$this->listener = $listener;
	}
	public function update(\SplSubject $subject){
		$listener = $this->listener;
		$listener($subject->value);
	}
}