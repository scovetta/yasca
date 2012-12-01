<?
declare(encoding='UTF-8');
namespace Yasca;
use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;

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
		$this->setOptions([
			'severity' => 5,
			'category' => 'General',
			'pluginName' => 'BuiltIn',
		]);
	}

	protected function setOption($key, $value){
		$this->$key =
			Operators::match($key,
				[
					Operators::curryTail(
						[Operators::_class,'in'],
						'lineNumber', 'severity'
					),
					Operators::paramLimit(
						Operators::curry('\intval', $value),
						0
					)
				],
				[
					Operators::curryTail(
						[Operators::_class,'in'],
						'unsafeSourceCode', 'references', 'unsafeData'
					),
					Operators::curry([Iterators::_class,'toArray'], $value, true)
				],
				[
					static function($key) use ($value){
						return Operators::in($key, 'message', 'description', 'pluginName', 'category') &&
							   \is_string($value);
					},
					Operators::identity($value)
				],
				[
					static function($key) use ($value){
						return Operators::in($key, 'filename') &&
							   \is_string($value);
					},
					Operators::identity($value)
				],
				[
					Operators::identity(true),
					static function($key) use ($value) {
						throw new \InvalidArgumentException("$key invalid with value $value");
					}
				]
			);
	}
}