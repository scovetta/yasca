<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ArbitraryFiles;

final class JAVA extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?xi)
	\b
	(
		#Constructors
		new \s+ (
			File
		) \s* \(
	|
		#JSP Include
		\<	jsp : include [^>]* request \.
	)
`u
EOT;
	}
}