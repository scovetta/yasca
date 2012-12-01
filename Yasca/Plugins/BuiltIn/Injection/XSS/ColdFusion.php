<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;

final class COLDFUSION extends \Yasca\Plugin {
	use Base, SourceSink;
	protected function getSourceRegexFragment(){return <<<'EOT'
		(
			#variable# assignment in form
			\# (
				url |
				form
			) \.
		)
EOT;
	}

	protected function getSinkRegexFragment(){return <<<'EOT'
		(
			#variable# assignment in form
			(
				=["']?
			)
		)
EOT;
	}
}