<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
use \Yasca\Plugins\BuiltIn\Injection\SourceRegexNet;

final class NET extends \Yasca\Plugin {
	use Base, SourceSink, SourceRegexNet{
		SourceRegexNet::getIdentifierRegexFragment insteadof SourceSink;
	}
	protected function getSinkRegexFragment(){return <<<'EOT'
((?xi)
	\b
	(
		#String concatenation
		(
			\. ( Append | AppendFormat | WriteLine ) \s* \( | #StringBuilder
			\+ |	#C#
			&		#VB.NET
		)
		#Direct writes
		(
			#Razor @, ASP.NET <%: and <%#: direct writes all HTML encode
			\<\%= |
			\<\%\#(?!:)
		) |
		#Function calls
		(
			#Calls to response
			Response \. (
				Write |
				WriteFile |
				Output \. (
					Write |
					WriteLine
				)
			)
		) \s* \(
	)
)
EOT;
	}
}