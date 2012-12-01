<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Obfuscation\Escaping;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
    protected function getRegex(){return <<<'EOT'
`(?x)
	\\u  \+ 00[0-7][a-fA-F0-9]
`u
EOT;
    }
}