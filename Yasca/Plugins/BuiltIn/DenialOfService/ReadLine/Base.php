<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\DenialOfService\ReadLine;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 4,
    		'category' => 'Denial of Service',
	        'description' => <<<'EOT'
The (Java) BufferedReader.readLine() function is dangerous because it will continue to
read data until an EOF marker is received. This could be used as a denial of service,
both because (a) the thread will continue to run for as long as data is being received,
and (b) all data received will be placed in memory (until the memory limit is reached).

Instead of BufferedReader.readLine(), the BufferedReader.read() function can be used
to limit the amount of data read.

Implementations of ReadLine in other platforms follow the same pattern.
EOT
,    		'references' => [
				'http://bugs.sun.com/bugdatabase/view_bug.do?bug_id=4071281' =>
					'BufferedReader.readLine bug report',
				'http://msdn.microsoft.com/en-us/library/system.console.readline.aspx' =>
					'MSDN: System.Console.ReadLine',
				'https://www.fortify.com/vulncat/en/vulncat/java/denial_of_service.html' =>
					'Fortify: VulnCat Java DOS',
            ],
	    ]);
    }
}