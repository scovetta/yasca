<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\StackTrace;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

    protected function getSupportedFileClasses(){return ['NET', 'asp', ];}

    protected function getRegex(){return <<<'EOT'
`(?xi)
	response \s* \. \s* write \s* \(
		.*	stacktrace
`u
EOT;
    }
}