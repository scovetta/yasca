<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
use \Yasca\Plugins\BuiltIn\Injection\SourceRegexPhp;

final class PHP extends \Yasca\Plugin {
	use Base, SourceSink, SourceRegexPhp{
		SourceRegexPhp::getIdentifierRegexFragment insteadof SourceSink;
	}

	protected function getSinkRegexFragment(){return <<<'EOT'
((?xi)
	\b
	#String concatenation
	(
		\.
	)
	#Direct writes
	(
		echo |
		print |
		\<\?=
	) |
	#Function calls
	(
		echo |
		print
	) \s* \(
)
EOT;
	}
}