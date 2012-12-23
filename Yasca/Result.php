<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Iterators;

/**
 * Result Class
 *
 * This struct holds result information for a particular issue found. There will be
 * one Result object created for each such issue.
 * @author Michael V. Scovetta <scovetta@users.sourceforge.net>
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 * @license see doc/LICENSE
 * @package Yasca
 */
final class Result {
	use \Yasca\Core\Options;

	public function __construct(){
		$this->setOption('severity',  5);
		$this->setOption('category',  'General');
		$this->setOption('pluginName','BuiltIn');
	}

	protected function setOption($key, $value){
		if 	     ($key === 'lineNumber' ||
				  $key === 'severity'
		){
			$this->$key = \intval($value);

		} elseif ($key === 'unsafeSourceCode' ||
				  $key === 'references' 	  ||
				  $key === 'unsafeData'
		){
			$this->$key = Iterators::toArray($value, true);

		} elseif ($key === 'message' 	 ||
				  $key === 'description' ||
				  $key === 'pluginName'  ||
				  $key === 'category'
		){
			if (\is_string($value) !== true){
				throw new \InvalidArgumentException("$key must be a string");
			}
			$this->$key = $value;

		} elseif ($key === 'filename'){
			if ($value !== null && \is_string($value) === true && $value !== ''){
				$this->filename = $value;
			}
		} else {
			throw new \InvalidArgumentException("$key is invalid");
		}
	}
}