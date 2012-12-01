<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\HardcodedKey;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b ( encryption | \s ) key \s* = \s* ["']
`u
EOT;
    }
}