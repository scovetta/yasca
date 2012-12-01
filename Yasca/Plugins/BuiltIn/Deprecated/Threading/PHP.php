<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\Threading;
use \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

final class PHP extends \Yasca\Plugin {
	use Base, SimpleFileContentsRegex {
		SimpleFileContentsRegex::getResultIterator as private getBaseResultIterator;
	}
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b declare \s* \( \s* ticks \s* =
`u
EOT;
	}

	public function getResultIterator($fileContents, $filepath){
		return (new \Yasca\Core\IteratorBuilder)
		->from($this->getBaseResultIterator($fileContents, $filepath))
		->select(static function($result){return $result->setOptions([
    			'references' => [
    				'http://php.net/manual/en/control-structures.declare.php' =>
    					'PHP: Declare directive',
    			],
	    	]);
    	});
    }
}