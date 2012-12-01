<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection;

trait SourceRegexPhp {
	protected function getIdentifierRegexFragment(){return <<<'EOT'
\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
EOT;
	}

	protected function getSourceRegexFragment(){return <<<'EOT'
((?xi)
	\b
	#Built-in variable access
	\$_ (

		SERVER		|
		GET			|
		POST		|
		FILES		|
		REQUEST 	|
		SESSION		|
		ENV			|
		COOKIE

	) |
	#Deprecated? Where does this come from?
	\$REQUEST_URI
)
EOT;
	}
}