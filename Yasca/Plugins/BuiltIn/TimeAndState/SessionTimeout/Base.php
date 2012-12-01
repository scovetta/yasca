<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\TimeAndState\SessionTimeout;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Session Management'
,	        'description' => <<<'EOT'
Do not use excessive web application session timeouts.
EOT
,    		'references' => [
				'https://www.owasp.org/index.php/Session_Timeout' =>
					'OWASP: Session Timeout',
            ],
	    ]);
    }
}
