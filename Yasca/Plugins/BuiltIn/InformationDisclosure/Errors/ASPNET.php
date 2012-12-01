<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\InformationDisclosure\Errors;

final class ASPNET extends \Yasca\Plugin {
	use Base, \Yasca\SingleFilePathPlugin;

    protected function getSupportedFileClasses(){return ['config', ];}

    public function getResultIterator($filepath){
    	$dom = new \DOMDocument();
        try {
        	$success = $dom->load($filepath);
        } catch (\ErrorException $e){
        	$success = false;
        }
        if ($success !== true){
        	$this->log(['Tried loading a .config file that is not an ASP.NET file.', \Yasca\Logs\Level::DEBUG]);
		    return new \EmptyIterator();
        }

        if ((new \DOMXPath($dom))->evaluate(<<<'EOT'
boolean(/configuration/system.web) and (
	not(boolean(system.web/customErrors)) or
	not(boolean(system.web/customErrors[@defaultRedirect])) or
	boolean(system.web/customErrors[@mode = 'Off'])
)
EOT
		) === true){
			return $this->newResult()->setOptions([
        		'filename' => "$filepath",
        	]);
        } else {
        	return new \EmptyIterator();
        }
	}
}