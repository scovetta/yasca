<?
$a = [1,2,3,];

//Bug
for ($i=0; $i <= \count($a); $i++){}

//Better; do not catch.
for($i=0; $i < \count($a); $i++){}
for($i=0,$c=\count($a); $i<$c; $i++){}
foreach($a as $key => $value){}
\array_walk($a, static function(){});
\iterator_apply(new \ArrayIterator($a), static function(){});