<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Random;

final class PYTHON extends \Yasca\Plugin {
    use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
    protected function getRegex(){return <<<'EOT'
`(?ix)
    \b random \s* \. \s* random \s* \(
`
EOT;
    }
}