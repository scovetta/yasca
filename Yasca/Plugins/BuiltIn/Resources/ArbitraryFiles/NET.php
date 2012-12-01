<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ArbitraryFiles;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?xi)
	\b (
		#Constructors
		new \s+ (
			File(Stream)?		|
			StreamReader
		) \s* \(

	|
		#File functions

		#http://msdn.microsoft.com/en-us/library/system.web.httpresponse.transmitfile.aspx
		Response \s* \. \s* TransmitFile \s* \(

	|
		File \s* \. \s* (
			Copy		|
			Exists		|
			Get			|
			Move		|
			Open		|
			Read
		)
	|
		#Directory modification
		Directory \s* \.

	)
`u
EOT;
	}
}