PHP MySQLi wrapper
==================

Introduction
------------

This is a simple PHP wrapper that uses MySQLi to generate simple SQL statements and save time doing so. 

It is non persistant (you connect and disconnect when you're finished).

Example use
-----------

Calling the constructor:

	$db = new DBEngine("root", "password", "database");

The following will create and execute the statement select * from users where username = "test" and password = "pass":

	$db->select("users", array(
					"username" => "test",
					"password" => "pass"
				)
	);

	// Get last array count
	echo $db->get_last_count();

	// Print the array holding the last result of a select statement
	var_dump($db->result);

The following will create and execute the statement delete from users where username = "KOL":

	$db->delete("users", array("username" => "KOL"), 'and', true);

The rest is in the comments of the class itself and the example file.
