<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authentication\Weak;

final class Plugin extends \Yasca\Plugin {
	use Base, \Yasca\SingleFileContentsPlugin;

	protected function getSupportedFileClasses(){
		return [
			'JAVA', 'JAVASCRIPT', 'C', 'HTML', 'PHP', 'NET',
			'PYTHON', 'PERL', 'COBOL', 'RUBY', 'TEXT',
		];
	}

	public function getResultIterator($fileContents, $filepath){
		return (new \Yasca\Core\IteratorBuilder)
    	->from($fileContents)
    	->whereRegex(<<<'EOT'
`(?ix)
	\b
	(?<prefix>	.{0,20}	)
	(
		user (
    		name |
    		id
    	)? |
    	logon (
    		id
    	)?
    )
    \s*		=	\s*
    (?<quote> ["']? )  (?<value>	[^\s"']+ )  \k{quote}
`u
EOT
    		, \RegexIterator::GET_MATCH
    	)
    	->selectMany(function($current, $key) use ($fileContents){
    		$asRegexLiteral = static function($literal){
    			$quoted = \preg_quote($literal, '`');
    			return "((?-ix)$quoted)";
    		};
			return (new \Yasca\Core\IteratorBuilder)
			->from($fileContents)
			->slice($key, 20)
			->whereRegex(<<<"EOT"
`(?ix){$asRegexLiteral($current['prefix'])}	pass(word)?

    \\s*=\\s*

    (?<quote> ["']? ){$asRegexLiteral($current['value'])}	 \\k{quote}
`u
EOT
			);
    	})
    	->select(function($current, $key) use ($filepath){
    		return $this->newResult()->setOptions([
	    		'lineNumber' => $key+1,
	    		'message' => "$current",
	    		'filename' => "$filepath",
			]);
	    });
	}
}