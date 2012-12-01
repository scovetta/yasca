<?
declare(encoding='UTF-8');
namespace Yasca\Logs;
use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;

/**
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class FileCsvLog extends \Yasca\Log {
	use \Yasca\Core\Closeable;

	protected function innerClose(){
		unset($this->fileObject);
	}

	const OPTIONS = <<<'EOT'
--log,FileCsvLog[,filename,levels]
filename: The name of the file to write, relative to the current working directory
levels: The numerical value of the level flags (DEBUG: 1, INFO: 2, ERROR: 4, ALL: 7)
EOT;

	private $fileObject;
	private $levels;
	public function __construct($args){
		$this->fileObject =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class, 'elementAt'], 0)
			->pipe([Operators::_class, '_new'], 'w', '\SplFileObject')
			->unwrap();

		$this->levels =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class,'elementAtOrNull'], 1)
			->pipe([Operators::_class, 'nullCoalesce'],
				(Level::DEBUG | Level::INFO | Level::ERROR)
			)
			->unwrap();
	}

	public function update(\SplSubject $subject){
		list($message, $severity) = $subject->value;
		if (($severity & $this->levels) !== $severity){
			return;
		} elseif ($severity === Level::DEBUG){
			$this->fileObject->fputcsv(['DEBUG', $message, \date(\DateTime::ISO8601),]);
		} elseif ($severity === Level::INFO){
			$this->fileObject->fputcsv(['INFO', $message, \date(\DateTime::ISO8601),]);
		} elseif ($severity === Level::ERROR){
			$this->fileObject->fputcsv(['ERROR', $message, \date(\DateTime::ISO8601),]);
		} else {
			//Ignore it.
		}
	}
}