<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\SentenceEndedEarly;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
	    	'severity' => 2,
	    	'category' => 'Sentence Ended Prematurely',
	    	'description' => <<<'EOT'
The period (.) ends a COBOL sentence.
In this case, it appears that the period was used accidentally, as in:
		IF CONDITION
			DO-SOMETHING
		ELSE.
			DO-SOMETHING-ELSE
EOT
,			'references' => [
				'http://www.computerworld.com/s/article/44582/Security_Alert_Moving_Cobol_to_the_Web_Safely' =>
					'Security Alert: Moving Cobol to the Web - Safely',
			],
	    ]);
	}
}