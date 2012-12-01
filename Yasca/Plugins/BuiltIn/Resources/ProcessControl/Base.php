<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ProcessControl;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
        	'severity' => 4,
        	'category' => 'Process Control',
        	'description' => <<<'EOT'
Process control functions are dangerous because it starts a new process with the same rights as the original.
EOT
,        	'references' => [
		    	'https://www.owasp.org/index.php/Process_Control' => 'OWASP: Process Control',
				'https://www.fortify.com/vulncat/en/vulncat/java/process_control.html' => 'Fortify: VulnCat Process Control',
		    ],
        ]);
	}
}