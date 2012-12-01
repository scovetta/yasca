<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\SQL;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;
use \Yasca\Plugins\BuiltIn\Injection\SourceRegexNet;

final class NET extends \Yasca\Plugin {
	use Base, SourceSink, SourceRegexNet {
		SourceRegexNet::getIdentifierRegexFragment insteadof SourceSink;
	}
	protected function getSinkRegexFragment(){return <<<'EOT'
((?xi)
	\b
	(
		#SQL keywords
		#Direct writes
		(update|insert|delete|select|merge)
		\s

		#string concat'ed
		("')	\s*		&
	)
)
EOT;
	}
}