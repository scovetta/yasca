<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\SQL;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
			'severity' => 1,
	        'category' => 'SQL Injection',
	        'references' => [
    			'https://www.fortify.com/vulncat/en/vulncat/java/sql_injection.html' =>
    				'Fortify: VulnCat SQL Injection',
	        	'http://en.wikipedia.org/wiki/SQL_injection' => 'Wikipedia: SQL Injection',
	        ],
	        'description' => <<<'EOT'
SQL injection is a code injection technique that exploits a security vulnerability
occurring in the database layer of an application. The vulnerability is present when
user input is either incorrectly filtered for string literal escape characters
embedded in SQL statements or user input is not strongly typed and thereby unexpectedly
executed. It is an instance of a more general class of vulnerabilities that can occur
whenever one programming or scripting language is embedded inside another. SQL injection
attacks are also known as SQL insertion attacks.
EOT
,		]);
    }
}