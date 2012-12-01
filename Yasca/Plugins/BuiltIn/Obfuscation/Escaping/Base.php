<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Obfuscation\Escaping;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
    		'severity' => 4,
    		'category' => 'Code Obfuscation',
	        'description' => <<<'EOT'
Use of character escapes for normal ASCII ranges is often unnecessary,
and can be used to obfuscate code. This is often done to avoid automated
tools from detecting malicious code.

Review uses of character escapes to ensure it does not hide malicious code.
EOT
,           'references' => [
	        	'http://www.blackhat.com/presentations/bh-usa-09/WILLIAMS/BHUSA09-Williams-EnterpriseJavaRootkits-PAPER.pdf' =>
	        		'Blackhat 2009 - Enterprise Java Rootkits',
	        ],
    	]);
    }
}