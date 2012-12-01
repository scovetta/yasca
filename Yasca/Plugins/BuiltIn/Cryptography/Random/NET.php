<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Random;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b new \s+ Random \s* \(
`u
EOT;
    }
}