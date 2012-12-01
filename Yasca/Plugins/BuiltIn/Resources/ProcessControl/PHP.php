<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\ProcessControl;

final class PHP extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?xi)
	\b
	(

		#http://php.net/manual/en/book.exec.php
		\`.*\` 	|
		(
			(shell_)?exec |
			proc_(
				open |
				close |
				get_status |
				nice |
				terminate
			)				|
			system
		) \s* \(
	)
`u
EOT;
    }
}