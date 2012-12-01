<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Operators;

abstract class Log implements \SplObserver {
	public static function getInstalledLogs(){
		return (new \Yasca\Core\FunctionPipe)
		->wrap(__DIR__ . '/Logs')
		->pipe([Operators::_class,'_new'], '\FilesystemIterator')
		->toIteratorBuilder()
		->whereRegex('`Log\.php$`ui', \RegexIterator::MATCH, \RegexIterator::USE_KEY)
		->select(static function($fileinfo){
			return $fileinfo->getBasename('.php');
		});
	}

	abstract public function update(\SplSubject $subject);
}