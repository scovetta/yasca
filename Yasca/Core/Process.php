<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Wraps \proc_open and \proc_close to ensure that a process
 * is closed when no longer used.
 *
 * In addition, provides Async methods, such as attaching a callback for
 * when the process completes.
 *
 * (PNCTL libraries are not available on Windows as of PHP 5.4.8)
 *
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
class Process extends Async {
	use Closeable;
	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	private static $maxStreamMemory = '';

	private $process;
	private $pipes = [];

	/**
	 * @param string $command
	 * @throws ProcessStartException
	 */
	public function __construct($command){
		//Create output and error streams that do not block after a certain size,
		//allowing the launched process to run to completion without waiting for
		//PHP to empty it's buffers.
		$stdoutTempStream = \fopen('php://temp' . self::$maxStreamMemory, 'rw');
		if ($stdoutTempStream === false){
			throw new ProcessStartException('Unable to open temporary stream for process standard in');
		}
		$this->pipes[] = $stdoutTempStream;
		$stderrTempStream = \fopen('php://temp' . self::$maxStreamMemory, 'rw');
		if ($stdoutTempStream === false){
			throw new ProcessStartException('Unable to open temporary stream for process standard out');
		}
		$this->pipes[] = $stderrTempStream;

        $pipes = [];
        try {
			$this->process = \proc_open(
				$command,
				[
					0 => ['pipe', 'r',],
		          	1 => $stdoutTempStream,
		          	2 => $stderrTempStream,
		        ],
		        $pipes,
		        null,
		        null,
		        [
		        	//This switch only takes effect on Windows machines
		        	'bypass_shell' => true,
		        ]
		    );
		    if ($this->process === false || \is_resource($this->process) !== true){
		    	throw new ProcessStartException('Unable to start process');
		    }
        } catch (\ErrorException $e){
        	$matches = [];
        	$match = \preg_match('`(?<=CreateProcess failed, error code - )\d+`u', $e->getMessage(), $matches);
        	if ($match === 1){
				throw new ProcessStartException(
					'Unable to start process, Windows error ' . $matches[0] .
					'. See http://msdn.microsoft.com/en-us/library/ms681381.aspx'
				);
        	} else {
        		throw new ProcessStartException('Unable to start process');
        	}
        }

        //$pipes[0] is the standard input stream created by \proc_open
        $pipes[1] = $stdoutTempStream;
		$pipes[2] = $stderrTempStream;
		$this->pipes = $pipes;

	    parent::__construct(
			//Instance anonymous function keeps this instance alive,
			//preventing PHP from closing the process.
			function(){
				return \proc_get_status($this->process)['running'] !== true;
			},
        	function(){
        		$this->closeStdIn();
        		return (new \Yasca\Core\IteratorBuilder)
        		->from($this->pipes)
        		->select(static function($pipe, $index){
        			$success = \rewind($pipe);
        			if ($success === false){
        				throw new \Exception("Unable to rewind process pipe $index");
        			}
        			return $pipe;
        		})
        		->select(static function($pipe, $index){
        			$contents = \stream_get_contents($pipe);
        			if ($contents === false){
        				throw new \Exception("Unable to get process data from process pipe $index");
        			}
        			return $contents;
        		})
        		->toArray();
        	}
        );

        $this->whenDone([$this,'close']);
	}

	public function writeToStdin($content){
		if (isset($this->pipes[0]) === false){
			throw new \Exception('Standard in already closed');
		}
		\fwrite($this->pipes[0], $content);
		return $this;
	}

	public function closeStdin(){
		if (isset($this->pipes[0]) === false){
			return;
		}
		$success = \fclose($this->pipes[0]);
		if ($success === false){
			throw new \Exception('Unable to close process pipe');
		}
		unset($this->pipes[0]);
		return $this;
	}

	protected function innerClose(){
		(new \Yasca\Core\IteratorBuilder)
		->from($this->pipes)
		->select(Operators::paramLimit('\fclose', 1))
		->where(Operators::curry([Operators::_class,'equals'], false))
		->toFunctionPipe()
		->pipe([Iterators::_class,'count'])
		->pipe([Operators::_class,'match'],
			[
				Operators::curry([Operators::_class,'equals'], 0),
				Operators::identity(null)
			],
			[
				Operators::identity(true),
				static function($failureCount){
					throw new \Exception("Unable to close $failuteCount process pipes");
				}
			]
		);

		if (\is_resource($this->process) === true){
			\proc_close($this->process);
		}
	}
}
\Closure::bind(
	static function(){
		//Allow up to 12 megabytes to be stored in RAM before going to disk.
		$megs = 12;
		$max = $megs * 1024 * 1024;
		static::$maxStreamMemory = "/maxmemory:$max";
	},
	null,
	__NAMESPACE__ . '\\' . \basename(__FILE__, '.php')
)->__invoke();