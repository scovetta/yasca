<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Weak;

final class Plugin extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getSupportedFileClasses(){return ['JAVA', 'COLDFUSION', 'C', ];}

	protected function getRegex(){return <<<'EOT'
`(?ix)
	(
		"(MD5|MD4|MD2|RC4|RC2|DES)" |

		#Suggests someone implemented MD5 themselves
		0xc040b340					|

		#Variants of DES
		DESEDE_ENCRYPTION_SCHEME	|
		DES_ENCRYPTION_SCHEME		|

		#XOR encryption
		#( [a-z0-9_]+ ) \[ [^\]]+ \g{-1} \.length	|

		#Coldfusion PDF encryption
		\<cfpdf (?= action="protect" ) (?! encrypt="AES_128" )
	)
`u
EOT;
    }
}