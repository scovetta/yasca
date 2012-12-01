<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Authorization\DebugParameter;

final class PHP extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	#http://php.net/manual/en/reserved.variables.php
	\$_ (

		SERVER		|
		GET			|
		POST		|
		FILES		|
		REQUEST 	|
		SESSION		|
		ENV			|
		COOKIE

	) \[  (?<opener> ["'] ) .* debug .* \k{opener} \]
`u
EOT;
	}
}