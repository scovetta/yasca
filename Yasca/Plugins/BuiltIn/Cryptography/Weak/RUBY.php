<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Weak;

final class RUBY extends \Yasca\Plugin {
    use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
    protected function getRegex(){return <<<'EOT'
`(?ix)
    :secret\s*\=\>\s*['"][^'"]{0,10}['"]\s*,?
`u
EOT;
    }
}