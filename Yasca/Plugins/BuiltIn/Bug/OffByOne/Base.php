<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Bug\OffByOne;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
    		'severity' => 2,
    		'category' => 'Bug: Possible Off By One Error in For Loop'
,	        'description' => <<<'EOT'
There appears to be an off-by-one error in a for loop. For example:

Foo[] foo = new Foo[10];
for (int i=0; i<=foo.length; i++){
	...
}

The code should be fixed to:

Foo[] foo = new Foo[10];
for (int i=0; i<foo.length; i++){
    ...
}

Alternatively, consider a for-each instead.
EOT
,    		'references' => [
	        	'http://en.wikipedia.org/wiki/Off-by-one_error' =>
    				'Wikipedia: Off by One error',
				'http://docs.oracle.com/javase/1.5.0/docs/guide/language/foreach.html' =>
					'Oracle: The For-Each Loop',
            ],
	    ]);
    }
}