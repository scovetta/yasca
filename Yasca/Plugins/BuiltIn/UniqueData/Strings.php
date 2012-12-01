<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\UniqueData;

final class Strings extends \Yasca\Plugin {
	use Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 5,
			'message' => 'Review these unique strings for sensitive data',
			'description' => <<<'EOT'
Review the unique strings for sensitive data,
such as usernames or passwords.
EOT
,			'category' => 'Unique Strings',
		]);
	}

    protected function getUniqueData($fileContents){
    	return (new \Yasca\Core\IteratorBuilder)
    	->from($fileContents)
    	->whereRegex(
    		<<<'EOT'
`(?x)
	(?<quote> ["'] ) (?<string> .+? ) (?<! \\ ) \k{quote}
`u
EOT
, 			\RegexIterator::GET_MATCH
		)
    	->select(static function($match){return $match['string'];});
    }
}