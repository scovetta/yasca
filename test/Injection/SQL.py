
def Foo(username):
    return User.objects.raw("select * from auth_user where username='%s'" % username)

def Bar(sql):
    return User.objects.raw(sql)