<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\ClassLoader;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
use \Yasca\Plugins\BuiltIn\Injection\SourceRegexJava;

final class JAVA extends \Yasca\Plugin {
	use Base, SourceSink, SourceRegexJava;
	protected function getSinkRegexFragment(){return <<<'EOT'
((?x)
	\b
	(
		Class	\s* \. \s* forName 		\s* \( 	|
		System  \s* \. \s* loadLibrary 	\s* \(
	)
)
EOT;
	}
}