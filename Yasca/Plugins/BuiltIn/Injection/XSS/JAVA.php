<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
use \Yasca\Plugins\BuiltIn\Injection\SourceRegexJava;

final class JAVA extends \Yasca\Plugin {
	use Base, SourceSink, SourceRegexJava;
	protected function getSinkRegexFragment(){return <<<'EOT'
((?x)
	#Direct writes
	(
		\<\%\s*=
	) |
	#Function calls
	(
		#Calls to out
		out \. (
			print |
			println
		)
	) \s* \(
)
EOT;
	}
}