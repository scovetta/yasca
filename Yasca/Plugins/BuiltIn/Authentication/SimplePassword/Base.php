<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authentication\SimplePassword;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 1,
    		'category' => 'Weak Credentials',
	        'description' => <<<'EOT'
It is important to use strong passwords that are difficult to guess.
This finding indicates that a password was chosen that falls into
one of the following categories:

* Repeated String (i.e. foo2foo)
* Password Matching UserID
* Blank UserID/Password
* Password Ending in "4ever"
* Seasonal Password (i.e. summer12)
* Common Password (based on reported "most common" passwords)

If these passwords are required to remain hard-coded, they should be changed to something
stronger (at least 128-bits of entropy).
EOT
,    		'references' => [
	            'https://www.owasp.org/index.php/Codereview-Authentication#Weak_Passwords_and_Password_Functionality' =>
    				'OWASP: Weak Passwords',
				'http://cwe.mitre.org/data/definitions/259.html' =>
					'CWE-259: Hard-Coded Password',
            ],
	    ]);
	}
}