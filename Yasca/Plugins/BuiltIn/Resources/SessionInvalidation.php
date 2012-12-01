<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources;
/**
 * The SessionInvalidation Plugin uses PHP to identify if any HTTP Session objects are created by the application.
 * If any HTTP Session objects are created, the plugin then checks for Session.Invalidate calls.  The presence of
 * HTTP Session objects and the absence of Session.Invalidate calls could indicate a session fixation vulnerability.
 *
 * Plugin by Josh Berry, 3/31/2009.
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class SessionInvalidation extends \Yasca\Plugin {
	use \Yasca\AggregateFileContentsPlugin;

    protected function getSupportedFileClasses(){return ['JAVA',];}

    private $createExists = false;
    private $invalidateExists = false;

    public function apply($fileContents){
    	if ($this->createExists === false){
    		$this->createExists =
    			(new \Yasca\Core\IteratorBuilder)
    			->from($fileContents)
    			->whereRegex(<<<'EOT'
`(?xi)
	\b
	(
		Session \s [a-zA-Z0-9_]	|
		LoginContext
	)
`u
EOT
				)
    			->any();
    	}

    	if ($this->invalidateExists === false){
    		$this->invalidateExists =
    			(new \Yasca\Core\IteratorBuilder)
    			->from($fileContents)
    			->whereRegex(<<<'EOT'
`(?xi)
	\b
	(
		Session\.Invalidate		|
		HttpSession\.Invalidate |
		\.Invalidate
	)	\s*	\(
`u
EOT
				)
    			->any();
    	}
    }

    public function getResultIterator(){
    	if ($this->createExists === true && $this->invalidateExists !== true){
    		return (new \Yasca\Result)->setOptions([
    			'severity' => 4,
    			'category' => 'Session Fixation',
    			'references' => [
    				'http://www.owasp.org/index.php/Session_Fixation' => 'OWASP: Session Fixation',
    			],
    			'description' => <<<'EOT'
Session objects created but no session invalidation found anywhere in code.
Authenticating a user without invalidating any existing session identifier
gives an attacker the opportunity to steal authenticated sessions.
EOT
, 			]);
    	} else {
    		return new \EmptyIterator();
    	}
    }
}
