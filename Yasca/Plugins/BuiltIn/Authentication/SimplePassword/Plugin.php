<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authentication\SimplePassword;

final class Plugin extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getSupportedFileClasses(){
		return [
			'JAVA', 'JAVASCRIPT', 'C', 'HTML', 'PHP', 'NET',
			'PYTHON', 'PERL', 'COBOL', 'RUBY', 'TEXT',
			'xml', 'properties', 'dev', 'qa', 'prod',
			'pilot', 'wsdd',
		];
	}

	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b
	(
		DSN=.*PWD=;				|
		DSN=.* (
			User\sId | ID
		)=([^;]+) .*
		(Password | PWD)=\g{-2}	|

		password [^=]* =
		.* (
			(summer|winter|fall|spring)(\d{2}|\d{4})	|
			thomas	|	arsenal	|	monkey	|	charlie |
			qwerty	|	123456	|	letmein	|	liverpool |
			password	|	123	|	abc123	| 	4ever	|
			(.{3,})    2    \g{-1}
		) .*
	)
`u
EOT;
	}
}