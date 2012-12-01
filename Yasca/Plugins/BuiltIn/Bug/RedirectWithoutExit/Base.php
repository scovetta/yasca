<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\RedirectWithoutExit;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
	    	'severity' => 4,
	    	'category' => 'Redirect Without Exit',
	    	'description' => <<<'EOT'
The web application sends a redirect to another location,
but instead of exiting, it executes additional code.
EOT
,			'references' => [
				'https://cwe.mitre.org/data/definitions/698.html' => 'CWE-698: Redirect Without Exit',
			],
	    ]);
    }
}