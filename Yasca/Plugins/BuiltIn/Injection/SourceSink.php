<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection;

trait SourceSink {
	use \Yasca\SingleFileContentsPlugin, \Yasca\Plugins\BuiltIn\Base;

	/** @return string */ abstract protected function getSourceRegexFragment();
	/** @return string */ abstract protected function getSinkRegexFragment();
	/** @return string */ protected function getIdentifierRegexFragment(){return '[a-zA-Z0-9\_]+';}

    public function getResultIterator($fileContents, $filename){
    	$q = static function($regexFragment){
    		return \preg_replace('`\``u', $regexFragment, '\$0');
		};
		$asRegexLiteral = static function($literal){
    		$quoted = \preg_quote($literal, '`');
    		return "((?-ix)$quoted)";
    	};

		return (new \Yasca\Core\IteratorBuilder)
		->from($fileContents)
		->whereRegex(<<<"EOT"
`(?x)
	(?<variable>  {$q($this->getIdentifierRegexFragment())}  )

	\\s*	=	\\s*

	{$q($this->getSourceRegexFragment())}
`u
EOT
			, \RegexIterator::GET_MATCH
		)
		->selectMany(function($match, $sourceLine) use ($q, $fileContents, $filename, $asRegexLiteral){
			return (new \Yasca\Core\IteratorBuilder)
			->from($fileContents)
			->skip($sourceLine+1)
		   	->whereRegex(<<<"EOT"
`(?x){$q($this->getSinkRegexFragment())}
	\\s*
	{$asRegexLiteral($match['variable'])}
`u
EOT
			)
		   	->select(function($unused, $sinkLine) use ($sourceLine, $filename){
		   		return $this->newResult()->setOptions([
    				'filename' => "$filename",
		        	'lineNumber' => $sourceLine+1,
    				'message' => "$fileContents[$sinkLine]",
		        	'unsafeData' => [
	            		'Source (line ' . ($sourceLine + 1) . '): ' => "$fileContents[$sourceLine]",
	            		'Sink (line ' . ($sinkLine + 1) . '): ' => "$fileContents[$sinkLine]",
	            	],
	            ]);
    		});
		})
		->concat(
			(new \Yasca\Core\IteratorBuilder)
			->from($fileContents)
			->whereRegex(<<<"EOT"
`(?x){$q($this->getSinkRegexFragment())}
	\\s*
	{$q($this->getSourceRegexFragment())}
`u
EOT
			)
			->select(function($current, $key) use ($filename){
				return $this->newResult()->setOptions([
	    			'filename' => "$filename",
		        	'lineNumber' => $key+1,
	        		'message' => "$current",
	            	'unsafeData' => [
		            	"Source and sink (line " . ($key + 1) . "): " => "$current",
		            ],
		        ]);
	        })
		);
    }
}