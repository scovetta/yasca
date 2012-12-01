<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection;

trait SourceRegexJava {
	protected function getSourceRegexFragment(){return <<<'EOT'
((?x)
	\b

	#Function calls

	#Calls to request
	( req(uest)? |
		getRequest\(\)
	) \. (
		getParameter |
		getRequestURI |
		getQueryString |
		getParameterNames |
		getCookies |
		getHeaderNames |
		getHeader |
		getAttribute
	)

	\s* \(
)
EOT;
	}
}