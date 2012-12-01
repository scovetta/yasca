<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * An emulation of asynchonous tasks/threads found in other platforms, such as Python.
 * Because all "async" calls are still synchronous within one PHP thread, it is best used when
 * spawning and monitoring processes or network connections while executing
 * other PHP code. (PNCTL libraries are not available on Windows as of PHP 5.4.8)
 *
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
class Async {
	private static $asyncs;


	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	/**
	 * Returns if there are any scheduled Asyncs
	 * @return bool
	 */
	public static function any(){
		return self::$asyncs->isEmpty();
	}

	/**
	 * Creates a new completed Async with the given resultt
	 */
	public static function fromResult($result){
		return new self(
			static function() { return true; },
			static function() use ($result) { return $result; }
		);
	}

	/**
	 * For each async tasks scheduled, execute its tick function synchronously.
	 * Executing the tick function on an Async will unschedule it if the Async completes.
	 * Do not use with declare(ticks=...) and register_tick_function
	 * as that feature is deprecated as of PHP 5.3.0.
	 * Instead, consider making a call for each event loop at the top level in your script.
	 * @return true|false True if there are currently scheduled Asyncs
	 */
	public static function tickAll(){
		//tickables() are allowed to register new tickables
		//Make sure that these are not lost.
		$snapshotOfAsyncs = self::$asyncs;
		self::$asyncs = Iterators::toList([]);
		self::$asyncs =
			(new \Yasca\Core\IteratorBuilder)
			->from($snapshotOfAsyncs)
			->where(static function($async){
				return $async->tick() === false;
			})
			->toFunctionPipe()
			->pipe([Iterators::_class,'toList'])
			->toIteratorBuilder()
			->concat(self::$asyncs)
			->toList();

		return self::$asyncs->isEmpty() === false;
	}

	private $done = false;

	public function isDone(){
		return $this->done;
	}

	public function isErrored(){
		return isset($this->e) === true;
	}

	private $e;
	private $resultValue = null;

	private $completions = null;

	/**
	 * Registers a function to be called immediately upon completion.
	 * If this Async is already completed, the function is invoked immediately.
	 * Triggers on both normal completion and on exception.-
	 * Returns this Async.
	 * @param callable $completion (Async $this)
	 * @return Async $this
	 */
	public function whenDone(callable $completion){
		if ($this->isDone() === true){
			$completion($this);
		} else {
			if (isset($this->completions) === false){
				$this->completions = new \SplDoublyLinkedList();
			}
			$this->completions->push($completion);
		}
		return $this;
	}

	public function continueWith(callable $asyncFactory){
		if ($this->isDone() === true){
			return $asyncFactory($this);
		}
		$getAsync = Operators::lazy(function() use ($asyncFactory){
			$async = $asyncFactory($this);
			while($async instanceof Wrapper){
				$async = $async->unwrap();
			}
			if (!($async instanceof Async)){
				throw new \UnexpectedValueException('Continuation factory did not return an Async');
			}
			return $async;
		});
		return new self(
			function() use ($getAsync) {
				return $this->isDone() === true &&
					$getAsync()->isDone() === true;
			},
			function() use ($getAsync) {
				return $getAsync()->result();
			}
		);
	}

	public function result(){
		if ($this->isDone() === false){
			//Busy loop for a short time, then fall back to sleep(1)
			for($i = 0; $i < 50; $i += 1){
				self::tickAll();
				if ($this->isDone() === true){
					break;
				}
			}
			while($this->isDone() === false){
				\sleep(1);
				self::tickAll();
			}
		}
		if ($this->isErrored() === false){
			return $this->resultValue;
		} else {
			throw $this->e;
		}
	}

	private $pollIsDone;
	private $getResultWhenDone;
	private $getResultWhenException;


	/**
	 * Poll this Async operation for completion, and if completed, set result and fire events.
	 * @return true|false
	 */
	protected function tick(){
		if ($this->done === false){
			try {
				$pollIsDone = $this->pollIsDone;
				$this->done = $pollIsDone();
			} catch (\Exception $e){
				$this->done = true;
				$this->e = $e;
			}
			if ($this->done === true){
				$getResultWhenDone = $this->getResultWhenDone;

				if ($this->isErrored() === false) {
					try {
						$this->resultValue = $getResultWhenDone();
					} catch (\Exception $e){
						$this->e = $e;
					}
				}
				if ($this->isErrored() === true && isset($this->getResultWhenException) === true){
					$getResultWhenException = $this->getResultWhenException;
					try {
						$this->resultValue = $getResultWhenException($this->e);
						unset($this->e);
					} catch (\Exception $e) {
						$this->e = $e;
					}
				}

				(new \Yasca\Core\IteratorBuilder)
				->from($this->completions)
				->forAll(function($completion){ $completion($this); });

				unset($this->completions);
				unset($this->pollIsDone);
				unset($this->getResultWhenDone);
				unset($this->getResultWhenException);
			}
		}
		return $this->done;
	}

	/**
	 * Creates a new Async class with the checker ($isDone) and the finisher ($getResultWhenDone)
	 * Exceptions thrown on $isDone are caught and rethrown on $this->result().
	 * @param callable $isDone No parameters. Returns true|false.
	 * 		Exceptions thrown are captured and sets $this->isErrored().
	 * 		Exceptions thrown set $this->isDone().
	 * @param callable $getResultWhenDone No parameters. Returns result for this async operation.
	 *      Exceptions thrown are captured and sets $this->isErrored().
	 * @param callable $getResultWhenException (\Exception). Returns result for this async operation.
	 * 		Optional. When present, clears $this->isErrored().
	 *      Exceptions thrown are captured and does not clear $this->isErrored(). This overwrites the previous exception.
	 */
	public function __construct(callable $pollIsDone, callable $getResultWhenDone, callable $getResultWhenException = null){
		$this->pollIsDone = $pollIsDone;
		$this->getResultWhenDone = $getResultWhenDone;
		$this->getResultWhenException = $getResultWhenException;
		if ($this->tick() === false){
			self::$asyncs->push($this);
		}
	}
}
\Closure::bind(
	static function(){
		static::$asyncs = new \SplDoublyLinkedList();
	},
	null,
	__NAMESPACE__ . '\\' . \basename(__FILE__, '.php')
)->__invoke();