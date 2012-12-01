<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authorization\DebugParameter;

final class ASPNET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getSupportedFileClasses(){return ['NET', 'ASP' ];}

	protected function getRegex(){return <<<'EOT'
`(?ix)
	\b
	#http://msdn.microsoft.com/en-us/library/h55b6cak.aspx
	Request \.  (

		#http://msdn.microsoft.com/en-us/library/system.web.httprequest.params.aspx
		QueryString |

		#http://msdn.microsoft.com/en-us/library/system.web.httprequest.querystring.aspx
		Params
	) \s*

	#VB.NET uses (), C# uses []
	(?<opener> \[ | \) ) .* debug .* \k{opener}
`u
EOT;
	}
}