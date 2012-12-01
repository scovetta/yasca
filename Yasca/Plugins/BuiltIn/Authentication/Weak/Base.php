<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authentication\Weak;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 1,
			'category' => 'Weak Credentials',
			'description' => <<<'EOT'
Passwords that match the associated username are extremely weak and should never be
used in a production environment, even if the password happens to meet the other
rules for password complexity. The username should never match the password.

For example, never use:
	foo-username = bar
	foo-password = bar
or
	foo-login = quux
	foo-pass = quux
EOT
,			'references' => [
				'https://www.owasp.org/index.php/Codereview-Authentication#Weak_Passwords_and_Password_Functionality' => 'OWASP: Weak Credentials',
			],
		]);
	}
}