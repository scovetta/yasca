<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\SentenceEndedEarly;

final class COBOL extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?x)
	\b	ELSE	\.
`u
EOT;
	}
}