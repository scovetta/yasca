<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\StackTrace;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
    		'severity' => 3,
    		'category' => 'Information Disclosure: Stack Trace',
	        'description' => <<<'EOT'
A stack trace can contain sensitive information about the state of an application, and
should never be sent to the client, even in a hidden or commented section of the page.

Proper log handling should be used to keep this information on the server and take
appropriate action.
EOT
,           'references' => [
	        	'https://www.owasp.org/index.php/Information_Leakage' =>
	        		'OWASP: Information Leakage',
	        ],
    	]);
    }
}