<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authorization\DebugParameter;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Debug Parameter',
	        'description' => <<<'EOT'
A debug parameter was found. This can be used within development, but should
never be accessible in a production system, unless appropriate access controls limit
the actions performed and data accessed to authorized individuals only.
Using a hard to guess parameter like debug4ever9123 is NOT sufficient.

This parameter should be removed.
EOT
,    		'references' => [
				'https://www.owasp.org/index.php/Leftover_Debug_Code' =>
    				'OWASP: Leftover Debug Code',
            ],
		]);
    }
}