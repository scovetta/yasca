<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\StackTrace;

final class JSP extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

    protected function getSupportedFileClasses(){return ['jsp', ];}

    protected function getRegex(){return <<<'EOT'
`(?x)
	\b printStackTrace \b
`u
EOT;
    }
}