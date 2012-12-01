<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\TimeAndState\LocalTime;

final class SQL extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?xi)
	\b
	(
		#Keywords
		(
			#http://msdn.microsoft.com/en-us/library/ms188751.aspx
			current_timestamp
		)
	|
		#Functions
		(
			#Microsoft SQL Server function calls

			#http://msdn.microsoft.com/en-us/library/ms188383.aspx
			#Replace with GETUTCDATE: http://msdn.microsoft.com/en-us/library/ms178635.aspx
			getdate 		|

			#http://msdn.microsoft.com/en-us/library/bb630353.aspx
			#Replace with SYSUTCDATETIME: http://msdn.microsoft.com/en-us/library/bb630387.aspx
			sysdatetime
		) \s* \(
	)
`u
EOT;
	}
}