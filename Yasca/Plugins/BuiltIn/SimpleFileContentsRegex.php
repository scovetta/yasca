<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn;

/**
 * Consolidates logic for simple file regex plugins
 */
trait SimpleFileContentsRegex {
	use Base, \Yasca\SingleFileContentsPlugin;

	abstract protected function getRegex();

	public function getResultIterator($fileContents, $filename){
		return (new \Yasca\Core\IteratorBuilder)
		->from($fileContents)
		->whereRegex($this->getRegex())
		->select(function($result, $key) use ($filename){
			return $this->newResult()->setOptions([
    			'filename' => "$filename",
            	'lineNumber' => $key + 1,
    			'message' => "$result",
	    	]);
    	});
    }
}