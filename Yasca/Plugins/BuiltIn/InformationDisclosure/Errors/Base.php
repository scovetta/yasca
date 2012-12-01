<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\Errors;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Error Leaking',
    		'message' => 'Missing Error Handling',
	        'description' => <<<'EOT'
Leaking information about the web application.

A stack trace can contain sensitive information about the state of an application, and
should never be sent to the client, even in a hidden or commented section of the page.

Further, default error messages (eg ASP.NET yellow screen of death) give out detailed
information about the system, and should be avoided.
EOT
,           'references' => [
				'http://cwe.mitre.org/data/definitions/12.html' =>
	            	'CWE-12: ASP.NET Missing Custom Error Page',
	        	'https://www.owasp.org/index.php/Information_Leakage' =>
	        		'OWASP: Information Leakage',
				'https://www.fortify.com/vulncat/en/vulncat/dotnet/asp_dotnet_misconfiguration_missing_error_handling.html' =>
					'Fortify: ASP.NET Missing Error Handling',
            ],
    	]);
    }
}