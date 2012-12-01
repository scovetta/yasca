<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\UniqueData;

final class Hosts extends \Yasca\Plugin {
	use Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 5,
			'message' => 'Review links to these unique hosts',
			'description' => <<<'EOT'
Files scanned contain URLs to these hosts.
Review these for inappropriate links, such as ftp://competitor.com
EOT
,			'category' => 'External Links',
		]);
	}

    protected function getUniqueData($fileContents){
		return (new \Yasca\Core\IteratorBuilder)
		->from($fileContents)
		->whereRegex(
			<<<'EOT'
`(?xi)
	#https://www.owasp.org/index.php/OWASP_Validation_Regex_Repository
    (
    	(((https?|ftps?|gopher|telnet|nntp)://)|(mailto:|news:))
    	(%[0-9A-Fa-f]{2}|[-()_.!~*';/?:@&=+$,A-Za-z0-9])+
    )
    ([).!';/?:,][[:blank:]])?
`u
EOT
, 			\RegexIterator::GET_MATCH
		)
		->select(static function($match){ return $match[0]; })
		->select(static function($url){
			return \parse_url($url, PHP_URL_HOST);
		});
    }
}