<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\Threading;
use \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

final class NET extends \Yasca\Plugin {
	use Base, SimpleFileContentsRegex{
		SimpleFileContentsRegex::getResultIterator as private getBaseResultIterator;
	}
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b (
		Thread .* \. \s* Abort \s* \(	|
		ThreadAbortException
	)
`u
EOT;
	}

	public function getResultIterator($fileContents, $filepath){
		return (new \Yasca\Core\IteratorBuilder)
		->from($this->getBaseResultIterator($fileContents, $filepath))
		->select(static function($result){return $result->setOptions([
    			'references' => [
					'http://msdn.microsoft.com/en-us/library/ty8d3wta.aspx' =>
						'MSDN: Thread.Abort',
    				'http://msdn.microsoft.com/en-us/library/system.threading.threadabortexception.aspx' =>
    					'MSDN: ThreadAbortException',
    			],
	    	]);
    	});
    }
}