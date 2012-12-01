<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ProcessControl;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getRegex(){return <<<'EOT'
`(?xi)
	\b
	(
		Process \s* \. \s* Start \s* \. \s* \(
	|
		new \s* Process \s* \(
	)
`u
EOT;
    }
}