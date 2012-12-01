<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\HardcodedKey;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Cryptography'
,	        'description' => <<<'EOT'
The use of a hardcoded cryptographic key in program source code is
considered a bad practice and should be avoided. A better technique
would be to load the key at runtime from a trusted store.
EOT
,    		'references' => [
	        	'https://www.owasp.org/index.php/Use_of_hard-coded_cryptographic_key' =>
    				'OWASP: Hard-coded Encryption Key',
            ],
	    ]);
    }
}