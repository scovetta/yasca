<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\UniqueData;
use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;

trait Base {
	use \Yasca\AggregateFileContentsPlugin, \Yasca\Plugins\BuiltIn\Base;

	protected function getSupportedFileClasses(){
		return [
			'JAVA', 'JAVASCRIPT', 'C', 'HTML', 'PHP', 'NET',
			'PYTHON', 'PERL', 'COBOL', 'RUBY', 'TEXT',
		];
	}

	abstract protected function getUniqueData($fileContents);

	private $uniqueData = [];

	public function getResultIterator(){
		return Operators::match($this->uniqueData,
			[
				[Iterators::_class,'any'],
				function($uniqueData){
					return $this->newResult()->setOptions([
						'unsafeData' => \array_keys($uniqueData),
					]);
				}
			],
			[
				Operators::identity(true), Operators::identity(new \EmptyIterator())
			]
		);
	}

    public function apply($fileContents){
    	foreach ($this->getUniqueData($fileContents) as $data){
    		$this->uniqueData[$data] = true;
    	}
    }
}