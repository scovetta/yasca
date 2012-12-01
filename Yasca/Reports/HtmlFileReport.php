<?
declare(encoding='UTF-8');
namespace Yasca\Reports;
use \Yasca\Core\Closeable;
use \Yasca\Core\Iterators;
use \Yasca\Core\JSON;
use \Yasca\Core\Operators;

final class HtmlFileReport extends \Yasca\Report {
	use Closeable;

	const OPTIONS = <<<'EOT'
--report,HtmlFileReport[,filename]
filename: The name of the file to write, relative to the current working directory
EOT;

	private $fileObject;
	private $firstResult = true;

	public function __construct($args){
		$this->fileObject =
			(new \Yasca\Core\FunctionPipe)
			->wrap($args)
			->pipe([Iterators::_class, 'elementAt'], 0)
			->pipe([Operators::_class, '_new'], 'w', '\SplFileObject')
			->unwrap();

		$c = static function(callable $c){return $c();};
		$this->fileObject->fwrite(<<<"EOT"
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<title>Yasca v{$c(static function(){return \Yasca\Scanner::VERSION;})} - Report</title>
<style type="text/css">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/bootstrap.min.css'))}
</style>
<style type="text/css">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/prettify.css'))}
</style>
<style type="text/css">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/HtmlFileReport.css'))}
</style>
<script type="text/javascript">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/jquery-1.8.2.min.js'))}
</script>
<script type="text/javascript">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/bootstrap.min.js'))}
</script>
<script type="text/javascript">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/HtmlFileReport.js'))}
</script>
<script type="text/javascript">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/prettify.js'))}
</script>
<script type="text/javascript">
    {$c(Operators::curry('\file_get_contents', __DIR__ . '/lib/showdown.js'))}
</script>
</head>
<body>
<div class="navbar navbar-inverse">
  <div class="navbar-inner">
    <ul class="nav">
      <li class="dropdown">
        <a href="#" class="brand dropdown-toggle" data-toggle="dropdown">
          Yasca
          <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
          <li><a href="#about-modal" role="button" data-toggle="modal">About</a></li>
          <li><a href="#" id="saveJson">Save</a></li>
          <li class="divider"></li>
          <li><a href="http://www.yasca.org/">Yasca Home Page</a></li>
          <li><a href="http://www.owasp.org/">OWASP</a></li>
        </ul>
      </li>
    </ul>
    <ul class="nav pull-right">
      <li><a href="#" class="brand">Output Report</a></li>
    </ul>
  </div>
</div>

<div id="about-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="about-modal-title">
    <div class="modal-header">
        <a class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
        <h3 id="about-modal-title">About Yasca</h3>
    </div>
    <div class="modal-body">
        <p>Yasca is a multi-platform static analysis tool designed to help
           developers create high-quality, secure software. More information
           can be found at <a href="http://yasca.org/">yasca.org</a>.</p>

        <table>
            <tr>
                <td><strong>Yasca Version:</strong></td>
                <td>{$c(static function(){return \Yasca\Scanner::VERSION;})}</td>
            </tr>
            <tr>
                <td><strong>Report Generated:</strong></td>
                <td>{$c(static function(){return \htmlspecialchars(\date(\DateTime::RFC850),ENT_NOQUOTES);})}</td>
            </tr>
        </table>
        <div style="clear:both;"></div>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal">Close</a>
    </div>
</div>

<h1 id="loading">Loading result <span id="loadingNum">0</span> of <span id="loadingOf">0</span></h1>
<div class="container-fluid">
  <div class="row-fluid">
    <div id="table-container" class="span12">
    </div>
  </div>
</div>
<div id="resultsJson" style="display:none">[
EOT
	);
}
	public function update(\SplSubject $subject){
		$result = $subject->value;
		if ($this->firstResult === true){
			$this->firstResult = false;
		} else {
			$this->fileObject->fwrite(',');
		}
		(new \Yasca\Core\FunctionPipe)
		->wrap($result)
		->pipe([JSON::_class,'encode'], JSON_UNESCAPED_UNICODE)
		->pipe('\htmlspecialchars', ENT_NOQUOTES)
		->pipe([$this->fileObject,'fwrite']);
	}

	protected function innerClose(){
		$this->fileObject->fwrite(<<<'EOT'
]</div>
</body>
</html>
EOT
		);
		unset($this->fileObject);
	}
}