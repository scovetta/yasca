<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\SQL;

final class PYTHON extends \Yasca\Plugin {
    use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

    protected function getSupportedFileClasses(){return ['PYTHON' ];}

    protected function getRegex(){return <<<'EOT'
`(?ix)
    \.objects\.raw\(
`u
EOT;
    }
}