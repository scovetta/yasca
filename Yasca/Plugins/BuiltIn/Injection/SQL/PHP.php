<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\SQL;
// use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
// use \Yasca\Plugins\BuiltIn\Injection\SourceRegexPhp;

final class PHP extends \Yasca\Plugin {
	// use Base, SourceSink, SourceRegexPhp {
		// SourceRegexPhp::getIdentifierRegexFragment insteadof SourceSink;
	// }
	
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	
	// protected function getSinkRegexFragment(){return <<<'EOT'
	protected function getRegex(){return <<<'EOT'
((?xi)
	(
		"(select|delete)\s.*from\s.*		|
		"insert\s+into\.*\s.*				|
		"update.*set.*
	)
)
EOT;
	}
}
