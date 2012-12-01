<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\Logging\Console;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?x)
	System	\.	(
		out	|
		err
	)	\.	print
`u
EOT;
    }
}