<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authorization\DebugParameter;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	\.	\s* getParameter .+ debug .* \)
`u
EOT;
	}
}