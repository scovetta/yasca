<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\Logging\Sensitive;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?x)
	log(ger)? \. .* \( .* (
		([^a-z]ssn[^a-z])	|
		(get)?ssn			|
		socialsecurity		|
		taxid				|
		e_?mail(address)?	|
		pass				|
		amount				|
		account				|
		acct				|
		address				|
		phone(number)?		|
		zip					|
		postal
	)
`u
EOT;
    }
}