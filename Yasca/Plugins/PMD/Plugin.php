<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\PMD;
use \Yasca\Core\Async;
use \Yasca\Core\Environment;
use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;
use \Yasca\Core\Process;
use \Yasca\Core\ProcessStartException;

/**
 * The PMD Plugin uses PMD to discover potential vulnerabilities in .java files.
 * This class is a Singleton that runs only once, returning all of the results that
 * first time.
 * @extends Plugin
 * @package Yasca
 */
final class Plugin extends \Yasca\Plugin {
    use \Yasca\MulticastPlugin;

    protected function getSupportedFileClasses(){return ['JAVA', 'jsp', ];}

    public function getResultIterator($path){
        if (Environment::hasAtLeastJavaVersion(4) !== true){
            $this->log(['PMD requires JRE 1.4 or later.', \Yasca\Logs\Level::ERROR]);
            return new \EmptyIterator();
        }

        try {

            $process = new Process(
                'java -cp "' .
                (new \Yasca\Core\FunctionPipe)
                ->wrap(__DIR__)
                ->pipe([Operators::_class,'_new'], '\FilesystemIterator')
                ->toIteratorBuilder()
                ->select(static function($u, $key){ return $key; })
                ->whereRegex('`\.jar$`ui')
                ->join(PATH_SEPARATOR) .
                '" net.sourceforge.pmd.PMD "' . $path . '"' .
                ' xml' .
                ' "' . __DIR__ . '/yasca-rules.xml"' //java-basic'
            );
            //    ' "' . __DIR__ . '/yasca-rules.xml"'
            //);
        } catch (ProcessStartException $e){
            $this->log(['PMD failed to start', \Yasca\Logs\Level::ERROR]);
            return new \EmptyIterator();
        }
        $this->log(['PMD launched', \Yasca\Logs\Level::INFO]);

        return $process->continueWith(function($async){
            list($stdout, $stderr) = $async->result();
            $this->log(['PMD completed', \Yasca\Logs\Level::INFO]);
            //$this->log([$stdout, \Yasca\Logs\Level::ERROR]);

            $dom = new \DOMDocument();
            try {
                $success = $dom->loadXML($stdout);
            } catch (\ErrorException $e){
                $success = false;
            }
            if ($success !== true){
                if ($stdout === ''){
                    $this->log(['PMD did not return any data', \Yasca\Logs\Level::ERROR]);
                    $this->log([$stderr, \Yasca\Logs\Level::ERROR]);
                } else {
                    $this->log(['PMD did not return valid XML', \Yasca\Logs\Level::ERROR]);
                    $this->log(["PMD returned $stdout", \Yasca\Logs\Level::ERROR]);
                }
                return Async::fromResult(new \EmptyIterator());
            }

            return (new \Yasca\Core\IteratorBuilder)
            ->from($dom->getElementsByTagName('file'))
            ->selectMany(static function($fileNode){
                return (new \Yasca\Core\IteratorBuilder)
                ->from($fileNode->getElementsByTagName('violation'))
                ->select(static function($violationNode) use ($fileNode){
                    return (new \Yasca\Result)->setOptions([
                        'pluginName' => 'PMD',
                        'filename' => "{$fileNode->getAttribute('name')}",
                        'lineNumber' => "{$violationNode->getAttribute('beginline')}",
                        'category' => "{$violationNode->getAttribute('rule')}",
                        'severity' => "{$violationNode->getAttribute('priority')}",
                        'description' => "", //Iterators::first($violationNode->getElementsByTagName('description'))->nodeValue,
                        'message' => "", //Iterators::first($violationNode->getElementsByTagName('message'))->nodeValue,
                        'references' => [
                            "{$violationNode->getAttribute('externalInfoUrl')}" => 'PMD Reference',
                        ],
                    ]);
                });
            })
            ->toFunctionPipe()
            ->pipe([Async::_class, 'fromResult']);
        });
    }
}