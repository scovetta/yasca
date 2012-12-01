
void getFoo() {
    char* x = allocSomeObject();
    if (x || x->foo > 10) {
	// do something
    } else if (x || x.foo > 10) {
	// do something
    } else if (!x && x->foo > 10) {
	// do something
    } else if (x != NULL && x->foo > 10) {
	// do something
    }
    
}