<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\OffByOne;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b for \s* \(
		.* = \s* 0 \s* ;
		.* \< = \s* .* \.length
`u
EOT;
    }
}