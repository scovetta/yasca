<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;

final class ASP extends \Yasca\Plugin {
	use Base, SourceSink;

	protected function getSupportedFileClasses(){return ['asp', ];}
	protected function getSourceRegexFragment(){return <<<'EOT'
((?xi)
	\b
	#Function calls
	(
		#Calls to request
		request \. (
			item |
			querystring |
			form
		) |
		#Calls to session
		(
			session
		)
	) \s* \(
)
EOT;
	}

	protected function getSinkRegexFragment(){return <<<'EOT'
((?xi)
	(
		#Direct writes
		(
			\<\%\s*=
		) |
		\b
		#Function calls
		(
			#Calls to response
			response \. (
				write |
				binarywrite
			)
		) \s* \(
	)
)
EOT;
	}
}