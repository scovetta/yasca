<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\SQL;
use \Yasca\Plugins\BuiltIn\Injection\SourceSink;

final class COLDFUSION extends \Yasca\Plugin {
	use Base, SourceSink;
	protected function getSourceRegexFragment(){return <<<'EOT'
((?xi)
	'? \# (	form | url )
)
EOT;
	}

	protected function getSinkRegexFragment(){return <<<'EOT'
((?xi)
	\b cfx_ingres\s.*"\s*(
		(select|delete) \s .* \s from	|
		insert \s into					|
		update \s .* \s set
	)\s.*
)
EOT;
	}
}