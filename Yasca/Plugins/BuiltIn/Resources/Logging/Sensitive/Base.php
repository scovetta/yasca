<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\Logging\Sensitive;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 3,
    		'category' => 'Potentially Logging Sensitive Information'
,	        'description' => <<<'EOT'
Log files are generally not treated the same as production data, even when sensitive information
is logged when an error occurs. Certain very sensitive information, such as social security
numbers or passwords, should never be logged.

From CERT.org:

	Logging is essential for gathering debugging information, carrying out incident response or
	forensics activities and for maintaining incriminating evidence. However, sensitive data
	should not be logged for many reasons. Privacy of the stakeholders, limitations imposed by
	the law on collection of personal information, and data exposure through insiders are a few
	concerns. Sensitive information includes and is not limited to IP addresses, user names and
	passwords, email addresses, credit card numbers and any personally identifiable information
	such as social security numbers. In JDK v1.4 and above, the java.util.logging class provides
	the basic logging framework.
EOT
,    		'references' => [
	           	'https://www.securecoding.cert.org/confluence/display/java/FIO30-J.+Do+not+log+sensitive+information' =>
    				'CERT: Do not log sensitive information',
				'https://www.owasp.org/index.php?title=Reviewing_Code_for_Logging_Issues&setlang=es' =>
					'OWASP: Reviewing code for logging issues',
            ],
	    ]);
    }
}