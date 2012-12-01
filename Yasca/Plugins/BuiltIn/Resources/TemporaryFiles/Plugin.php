<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Resources\TemporaryFiles;
use \Yasca\Core\JSON;

final class Plugin extends \Yasca\Plugin {
	use \Yasca\MulticastPlugin;

	protected function getSupportedFileClasses(){return [];}
	public function supportsExtension($ext){return true;}

	public function getResultIterator($path){
		$patterns = JSON::decode(
			\file_get_contents(__FILE__ . '.json'),
			true
		);

		return (new \Yasca\Core\IteratorBuilder)
		->from(new \RecursiveDirectoryIterator($path))
		->select(static function($current, $key) use (&$patterns){
    		foreach($patterns['matchExceptionPatterns'] as $pattern){
    			if (\fnmatch($pattern, $key)){
    				return null;
    			}
    		}
    		foreach($patterns['matchOncePatterns'] as $pattern => $description){
    			if (\fnmatch($pattern, $key)){
    				$arr =& $patterns['matchOncePatterns'];
    				unset($arr[$pattern]);
    				return $description;
    			}
    		}
    	    foreach($patterns['matchMultiplePatterns'] as $pattern => $description){
    			if (\fnmatch($pattern, $key)){
    				return $description;
    			}
    		}
    		return null;
    	})
    	->where(static function($description){return $description !== null;})
    	->select(static function($description, $filename){
    		return (new \Yasca\Result)->setOptions([
            	'filename' => "$filename",
    			'severity' => 3,
    			'category' => 'Potentially Sensitive Data Visible',
    			'description' => <<<'EOT'
Temporary, backup, or hidden files should not be included in a production site
because they can sometimes contain sensitive data, including:
source code (e.g. index.php.old), a list of other files (e.g. harvest.sig, .svn)
, and Deployment information (e.g. .project).
These files should be removed from the source tree,
or at least prior to a production rollout.
EOT
,				'references' => [
					'https://www.owasp.org/index.php/Guessed_or_visible_temporary_file' => 'OWASP: Guessed or Visible Temporary File',
					'https://www.owasp.org/index.php/Sensitive_Data_Under_Web_Root' => 'OWASP: Sensitive Data Under Web Root',
				],
				'message' => "$description files should not be visible on a production site.",
			]);
    	});
    }
}