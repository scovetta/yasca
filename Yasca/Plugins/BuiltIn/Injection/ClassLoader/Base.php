<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\ClassLoader;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

    protected function newResult(){
    	return (new \Yasca\Result)->setOptions([
    		'severity' => 1,
    		'category' => 'Class Loading Injection',
	        'description' => <<<'EOT'
The Class.forName() function is dangerous because it brings a new Java class into the JVM. If an attacker were able to control input
passed into this function, it would be possible to change the execution of the application.

Only load classes from trusted sources, and never use input data directly into Class.forName();

The System.loadLibrary() function is dangerous because it brings a native executable (shared object or DLL) into the JVM. This library
could potentially execute functions that that application wouldn't normally have access to. (Native executables are not bound by the JVM
security restrictions.)

Similarly, include() and require() in PHP, and similar features in other runtimes. Only load libraries from trusted sources.
EOT
,           'references' => [
	        	'https://www.owasp.org/index.php/Process_Control' =>
	        		'OWASP: Process Control',
				'https://www.owasp.org/index.php/Invoking_untrusted_mobile_code' =>
					'OWASP: Invoking untrusted mobile code',
				'http://www.blackhat.com/presentations/bh-usa-09/WILLIAMS/BHUSA09-Williams-EnterpriseJavaRootkits-PAPER.pdf' =>
					'Java Enterprise Rootkits',
	        ],
    	]);
    }
}