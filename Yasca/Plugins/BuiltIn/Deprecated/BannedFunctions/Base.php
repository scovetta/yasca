<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\BannedFunctions;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 2,
			'category' => 'Banned Function',
			'description' => <<<'EOT'
A function call was found that has been labeled as "unsafe" or "banned". Specifically, the function is now
considered banned by Microsoft. More information is available in The Security Development Lifecycle by
Michael Howard and Steve Lipner, Microsoft Press, 2006.

These functions should be replaced with safer alternatives, or at the minimum, verified that they are being used
in a safe manner.
EOT
,			'references' => [
				'http://msdn.microsoft.com/en-us/library/bb288454.aspx' =>
					'MSDN: Banned Functions',
				'http://www.usenix.org/events/usenix99/full_papers/millert/millert_html/index.html' =>
					'strlcpy and strlcat - Consistent, Safe String Copy and Concatenation',
				'https://www.owasp.org/index.php/Dangerous_Function' =>
					'OWASP: Dangerous Function',
			],
		]);
	}
}