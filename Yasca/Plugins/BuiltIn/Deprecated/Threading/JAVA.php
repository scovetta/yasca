<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\Threading;
use \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

final class JAVA extends \Yasca\Plugin {
	use Base, SimpleFileContentsRegex{
		SimpleFileContentsRegex::getResultIterator as private getBaseResultIterator;
	}
	protected function getRegex(){return <<<'EOT'
`(?x)
	\b
	Thread .* \. \s* (
		resume	|
		suspend |
		stop	|
		destroy
	) \s* \(
`u
EOT;
	}

	public function getResultIterator($fileContents, $filepath){
		return (new \Yasca\Core\IteratorBuilder)
		->from($this->getBaseResultIterator($fileContents, $filepath))
		->select(static function($result){return $result->setOptions([
    			'references' => [
					'http://docs.oracle.com/javase/6/docs/technotes/guides/concurrency/threadPrimitiveDeprecation.html' =>
    					'Oracle: Java Thread Primitive Deprecation',
    			],
	    	]);
    	});
    }
}