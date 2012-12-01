<?
declare(encoding='UTF-8');
namespace Yasca\Reports;
use \Yasca\Core\Closeable;
use \Yasca\Core\Iterators;
use \Yasca\Core\JSON;
use \Yasca\Core\Operators;

final class JsonFileReport extends \Yasca\Report {
	use Closeable;

	const OPTIONS = <<<'EOT'
--report,JsonFileReport[,filename,jsonEncodingFlags]
filename: The name of the file to write, relative to the current working directory
jsonEncodingFlags: The numerical value of the json encoding flags from
	http://php.net/manual/en/function.json-encode.php
EOT;

	private $flags;
	private $firstResult = true;
	private $fileObject;

	public function __construct($args){
		$this->fileObject =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class, 'elementAt'], 0)
			->pipe([Operators::_class, '_new'], 'w', '\SplFileObject')
			->unwrap();

		$this->flags =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class, 'elementAt'], 1)
			->pipe([Operators::_class, 'nullCoalesce'], JSON_UNESCAPED_UNICODE)
			->unwrap();

		$this->fileObject->fwrite('[');
	}

	protected function innerClose(){
		$this->fileObject->fwrite(']');
		unset($this->fileObject);
	}

	public function update(\SplSubject $subject){
		$result = $subject->value;
		if ($this->firstResult === true){
			$this->firstResult = false;
		} else {
			$this->fileObject->fwrite(',');
		}
		(new \Yasca\Core\FunctionPipe)
		->wrap($result)
		->pipe([JSON::_class,'encode'], $this->flags)
		->pipe([$this->fileObject, 'fwrite']);
	}
}