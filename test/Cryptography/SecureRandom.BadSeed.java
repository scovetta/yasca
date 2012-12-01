
class Foo {
    public void someFunction() {
	SecureRandom foo = new SecureRandom();
	long t0 = System.currentTimeMillis();
	
	
	
	foo.setSeed(t0);
    }
}