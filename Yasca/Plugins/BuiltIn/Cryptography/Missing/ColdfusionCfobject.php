<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Missing;

final class ColdfusionCfobject extends \Yasca\Plugin {
	use \Yasca\Plugins\BuiltIn\Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

	protected function getSupportedFileClasses(){return [ 'COLDFUSION', ];}

	protected function getRegex(){return <<<'EOT'
`(?xi)
	\<cfobject \s (?= [^\>]* type="(\.|dot)net" ) (?! [^\>]* secure="yes" )
`u
EOT;
	}

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
	    	'severity' => 3,
	    	'category' => 'Insecure Object Call',
	    	'description' => <<<'EOT'
When calling a .NET object on another server, use the 'secure' attribute to specify that
SSL should be used. Without this, the data passed back and forth could be viewable to
attackers.
EOT
,			'references' => [
				'http://livedocs.adobe.com/coldfusion/8/htmldocs/help.html?content=Tags_m-o_09.html' =>
					'Adobe: CF8 cfobject',
				'http://help.adobe.com/en_US/ColdFusion/9.0/CFMLRef/WSc3ff6d0ea77859461172e0811cbec22c24-7f6e.html' =>
					'Adobe: CF9 cfobject',
			],
	    ]);
	}
}