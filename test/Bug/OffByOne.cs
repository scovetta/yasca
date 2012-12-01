using System;

public class Class1
{
	public Class1()
	{
        var things = new[]{1,2,3,};
        for (var i = 0; i <= things.Length; i++) {
            var thing = things[i];
        }

        //Better, do not catch
        foreach (var thing in things) {

        }
	}
}
