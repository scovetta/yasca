<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Injection\XSS;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
	    	'severity' => 1,
        	'category' => 'Cross Site Scripting',
        	'references' => [
	        	'https://www.owasp.org/index.php/Cross_Site_Scripting' => 'OWASP: XSS Vulnerability',
	            'https://www.owasp.org/index.php/XSS' => 'OWASP: XSS Attack',
	            'http://www.ibm.com/developerworks/tivoli/library/s-csscript/' => 'IBM: Cross-site scripting',
	        ],
	        'description' => <<<'EOT'
Cross-Site Scripting (XSS) vulnerabilities can be exploited by an attacker to
impersonate or perform actions on behalf of legitimate users.

This particular issue is caused by writing user-controlled data directly to the page.
For instance, consider the following snippet:
	<%=request.getParameter("q")%>.

The attacker could exploit this vulnerability by directing a victim to visit a URL
with specially crafted JavaScript to perform actions on the site on behalf of the
attacker, or to simply steal the session cookie.

A solution to this problem would be HtmlEncoding at the sink.
EOT
,		]);
	}
}