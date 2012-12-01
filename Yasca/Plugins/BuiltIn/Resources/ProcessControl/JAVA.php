<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ProcessControl;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getRegex(){return <<<'EOT'
`(?x)
	\b
	\. \s* getRuntime \s* \( \s* \) \s* \. \s* exec \s* \(
`u
EOT;
    }
}