<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\MemoryLeak;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
        	'severity' => 2,
        	'category' => 'Unreleased Resource',
			'message' => 'GETMAIN calls exceed FREEMAIN calls',
        	'description' => <<<'EOT'
Resources were acquired (mutexes, file handles, memory) but not released properly.

In COBOL, there are more GETMAIN calls than there are FREEMAIN calls.
EOT
,        	'references' => [
		    	'http://www.owasp.org/index.php/Unreleased_Resource' => 'OWASP: Unreleased Resource',
		    ],
        ]);
	}
}