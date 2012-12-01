<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ConstantMemoryAllocation;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 5,
    		'category' => 'Constant Memory Allocation',
	        'description' => <<<'EOT'
Memory allocation functions should normally return results dependent on data type
sizes (i.e. sizeof(int) instead of "4"). Requesting a constant amount of memory can
make the solution non-portable.

This can also be an indicator of a nearby buffer overflow vulnerability.
EOT
,    		'references' => [
	        	'http://en.wikipedia.org/wiki/Malloc' =>
    				'Wikipedia: Malloc',
            ],
    	]);
    }
}
