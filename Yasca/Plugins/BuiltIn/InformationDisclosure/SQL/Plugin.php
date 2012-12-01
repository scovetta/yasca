<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\SQL;

final class Plugin extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;

    protected function getSupportedFileClasses(){return ['COLDFUSION','JAVA',];}

    protected function getRegex(){return <<<'EOT'
`(?ix)
	\b
    	(
    		#SQL Server DML: http://msdn.microsoft.com/en-us/library/ff848766.aspx
    		BULK\sINSERT |
    		DELETE 		 |
    		FROM 		 |
    		INSERT 		 |
    		MERGE 		 |
    		OPTION		 |
    		OUTPUT		 |
    		SELECT		 |
    		TOP			 |
    		UPDATE		 |
    		WHERE		 |
    		WITH
    	)
    	\s+
    	(
    		\*	|
    		into|
    		from
    	)
    	\s+
`u
EOT;
    }
}