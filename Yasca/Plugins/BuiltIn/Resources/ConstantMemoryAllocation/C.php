<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ConstantMemoryAllocation;

final class C extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`^(?!\/\/)(?:.(?!\/\/))*?\b(malloc|alloc|realloc)\(\s*\d+\s*\)`u
EOT;
    }
}