<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Cryptography\Weak;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Cryptography',
	        'description' => <<<'EOT'
Weak cryptographic functions and keys are dangerous because encrypted data
could be discovered by an adversary.

*Weak Algorithms*
Certain cryptographic algorithms are particularly weak and should be considered
deprecated. Applications that use these algorithms should establish a plan to
migrate to a new, stronger algorithm. New applications should choose a strong
algorithm, and build-in algorithm agility to allow the simple migration to
strong functions when necessary.


<table class="table table-condensed table-striped" style="width:auto">
    <thead>
        <tr>
            <th>If you are using...</th>
            <th>Then migrate to...</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2"><strong>Hash Algorithms</strong></td>
        </tr>
        <tr>
            <td>MD4</td>
            <td>SHA-256</td>
        </tr>
        <tr>
            <td>MD5</td>
            <td>SHA-256</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Symmetic Algorithms</strong></td>
        </tr>
        <tr>
            <td>DES</td>
            <td>AES-256</td>
        </tr>
        <tr>
            <td>3DES</td>
            <td>AES-256</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Asymmetic Algorithms</strong></td>
        </tr>
        <tr>
            <td>3DES</td>
            <td>AES-256</td>
        </tr>

    </tbody>
</table>

*Weak Keys*

Certain cryptographic algorithms such as MD5 are considered deprecated and
should not be used in any new applications. Current applications should consider migrating to current algorithms such as
AES and SHA-256.
EOT
,    		'references' => [
				'http://www.owasp.org/index.php?title=Using_a_broken_or_risky_cryptographic_algorithm' =>
					'OWASP: Risky or Broken Cryptography',
            ],
	    ]);
    }
}