<?
declare(encoding='UTF-8');
namespace Yasca\Logs;
use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;

/**
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class ConsoleLog extends \Yasca\Log {
	const OPTIONS = <<<'EOT'
--log,ConsoleLog[,levels]
levels: The numerical value of the level flags (DEBUG: 1, INFO: 2, ERROR: 4, ALL: 7)
EOT;
	private $levels;
	public function __construct($args = []){
		$this->levels =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class,'elementAtOrNull'], 0)
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
			print("DEBUG  $message\n");
		} elseif ($severity === Level::INFO){
			print("INFO   $message\n");
		} elseif ($severity === Level::ERROR){
			print("ERROR  $message\n");
		} else {
			//Ignore it.
		}
	}
}