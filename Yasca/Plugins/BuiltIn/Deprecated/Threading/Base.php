<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\Threading;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 4,
    		'category' => 'Unsafe or Deprecated Threading',
	        'description' => <<<'EOT'
Migrate away from unsafe threading features.
EOT
,
		]);
    }
}