<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\MemoryLeak;

/**
 * This class looks for GETMAIN/FREEMAIN resource leaks in COBOL source code.
 * @extends Plugin
 * @package Yasca
 */
final class COBOL extends \Yasca\Plugin {
	use Base, \Yasca\SingleFileContentsPlugin;
    public function getResultIterator($fileContents, $filepath){
        $unreleasedResources =
        	(new \Yasca\Core\IteratorBuilder)
        	->from($fileContents)
        	->whereRegex('`GETMAIN`u')
        	->count()
        	-
        	(new \Yasca\Core\IteratorBuilder)
        	->from($fileContents)
        	->whereRegex('`FREEMAIN`u')
        	->count();


        if ($unreleasedResources !== 0){
        	return $this->newResult()->setOptions(['filename' => "$filepath"]);
        } else {
        	return new \EmptyIterator();
        }
    }
}