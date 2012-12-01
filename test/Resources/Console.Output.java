class A {

    public void foo() {
	try {
	    A.bar();
	} catch(Exception ex) {
	    System.err.println(ex.getMessage());
	}
    }

    public void bar() throws Exception {

    }

}