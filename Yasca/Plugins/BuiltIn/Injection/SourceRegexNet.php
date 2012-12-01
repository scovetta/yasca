<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection;

trait SourceRegexNet {
	protected function getIdentifierRegexFragment(){return <<<'EOT'
((?x)
	#http://msdn.microsoft.com/en-us/library/aa664670.aspx

	@?

	( _ | \p{Lu} | \p{Ll} | \p{Lm} | \p{Lo} | \p{Nl} )

	( \p{Lu} | \p{Ll} | \p{Lm} | \p{Lo} | \p{Nl} |
	  \p{Mn} | \p{Mc} | \p{Nd} | \p{Pc} | \p{Cf}  )+
)
EOT;
	}
	protected function getSourceRegexFragment(){return <<<'EOT'
((?xi)
	\b
	#VB.NET is case insensitive
	#Member Access
	(
		#Calls to request
		(  HttpContext\.Current\. |
		   Page \.  |
		   this \.  |
		   Me \.
		 )? Request \. (
			#any call or member access
			[a-zA-Z]+
		)
	)
)
EOT;
	}
}