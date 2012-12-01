<?
declare(encoding='UTF-8');
namespace Yasca\Reports;
use \Yasca\Core\Closeable;
use \Yasca\Core\Iterators;
use \Yasca\Core\JSON;
use \Yasca\Core\Operators;

final class JsonStderrReport extends \Yasca\Report {
	use Closeable;

	const OPTIONS = <<<'EOT'
--report,JsonStderrReport[,jsonEncodingFlags]
jsonEncodingFlags: The numerical value of the json encoding flags from
	http://php.net/manual/en/function.json-encode.php
EOT;

	private $flags;
	private $firstResult = true;

	protected function innerClose(){
		\fwrite(STDERR, ']');
	}

	public function __construct($args){
		$this->flags =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class, 'elementAt'], 0)
			->pipe([Operators::_class,'nullCoalesce'], JSON_UNESCAPED_UNICODE)
			->unwrap();

		\fwrite(STDERR, '[');
	}

	public function update(\SplSubject $subject){
		$result = $subject->value;
		if ($this->firstResult === true){
			$this->firstResult = false;
		} else {
			\fwrite(STDERR,',');
		}
		(new \Yasca\Core\FunctionPipe)
		->wrap($result)
		->pipe([JSON::_class,'encode'], $this->flags)
		->pipeLast('\fwrite', STDERR);
	}
}