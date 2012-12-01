<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\TimeAndState\LocalTime;

final class NET extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?ix)
	(
		#Access to System.DateTime
		#http://msdn.microsoft.com/en-us/library/system.datetime.aspx
		DateTime \s*	\. \s* (
			#Properties
			(	Now		)
		|
			#Methods
			(
				FromFileTime |
				ToFileTime
			) \s* \(
		)
	)
`u
EOT;
	}
}