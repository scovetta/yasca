<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\Logging\Console;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?xi)
	Console \s* \. \s* Write(Line)? \s* \(
`u
EOT;
    }
}