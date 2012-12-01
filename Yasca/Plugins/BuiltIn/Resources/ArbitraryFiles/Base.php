<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ArbitraryFiles;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 3,
			'category' => 'Arbitrary File Disclosure',
			'description' => <<<'EOT'
This finding indicates what appears to be loading arbitrary files.
This includes executing the file (eg jsp include) and printing it to the user.

It should be noted that these types of accesses object performs path canonicalization.
This means that "foo" will look in the local directory.
This also means that "../../foo" will traverse up the current directory,
	such as to files that users should not see.

The typical attack against this type of vulnerbaility is to have the application
disclose a sensitive file, such as "../../../../etc/passwd".
EOT
,			'references' => [
				'https://www.owasp.org/index.php/Path_Traversal' => 'OWASP: Path Traversal',
				'https://cwe.mitre.org/data/definitions/22.html' => 'CWE-22: Path Traversal',
			],
		]);
	}
}