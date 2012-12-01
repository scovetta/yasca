<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\DenialOfService\ReadLine;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b ( ReadLine | ReadToEnd ) \s* \(
`
EOT;
    }
}