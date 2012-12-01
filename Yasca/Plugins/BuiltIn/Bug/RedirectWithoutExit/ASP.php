<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\RedirectWithoutExit;

final class ASP extends \Yasca\Plugin {
	use Base, \Yasca\SingleFileContentsPlugin;

    protected function getSupportedFileClasses(){return ['asp', 'vb', ];}

    public function getResultIterator($fileContents, $filepath){
    	return (new \Yasca\Core\IteratorBuilder)
    	->from($fileContents)
    	->whereRegex('`\bResponse\.Redirect\s*\(`iu')
    	->where(static function($u, $key) use ($fileContents){
    		return (new \Yasca\Core\IteratorBuilder)
    		->from($fileContents)
    		->slice($key, 2)
    		->whereRegex('`\bResponse\.End\s*\(`iu')
    		->any() !== true;
	    })
	    ->select(function($current, $key){
	    	return $this->newResult()->setOptions([
	    		'lineNumber' => $key + 1,
	    		'filename' => "$filepath",
	    	]);
	    });
    }
}