<?

$a = [];
if (isset($a[foo])){
	print 'bad';
}
if (isset($a['foo'])){
	print 'ok';
}