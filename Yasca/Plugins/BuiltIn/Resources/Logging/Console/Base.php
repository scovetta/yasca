<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\Logging\Console;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 5,
    		'category' => 'Poor Logging Practice',
	        'description' => <<<'EOT'
Using the System.out.print(ln) or System.err.print(ln) functions is considered a bad practice.
Instead, a logger should be used throughout the application for all log output.
EOT
,    		'references' => [
	           	'https://www.owasp.org/index.php?title=Poor_Logging_Practice:_Use_of_a_System_Output_Stream' =>
    				'OWASP: Poor Logging Practice',
				'http://logging.apache.org/log4j' => 'Log4j',
            ],
	    ]);
    }
}