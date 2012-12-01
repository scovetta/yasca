<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\SQL;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
    		'severity' => 3,
    		'category' => 'System Information Leak: SQL Statement',
	        'description' => <<<'EOT'
Leaking information about the database structure
EOT
,           'references' => [
	              'http://www.fortifysoftware.com/vulncat/java/java_encapsulation_sys_info_leak_html_comment_jsp.html' => 'leak',
	           ],
    	]);
    }
}