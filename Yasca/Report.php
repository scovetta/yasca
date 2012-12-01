<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Operators;

/**
 * Report Class
 *
 * This (abstract) class is the parent of the specific report renderers. It handles
 * the output stream creation, sorting, and other housekeeping details.
 * @author Michael V. Scovetta <scovetta@users.sourceforge.net>
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 * @license see doc/LICENSE
 * @package Yasca
 */
abstract class Report implements \SplObserver {
	public static function getInstalledReports() {
		return (new \Yasca\Core\FunctionPipe)
		->wrap(__DIR__ . '/Reports')
		->pipe([Operators::_class,'_new'], '\FilesystemIterator')
		->toIteratorBuilder()
		->whereRegex('`Report\.php$`ui', \RegexIterator::MATCH, \RegexIterator::USE_KEY)
		->select(static function($fileinfo){
			return $fileinfo->getBasename('.php');
		});
	}

	abstract public function update(\SplSubject $subject);
}