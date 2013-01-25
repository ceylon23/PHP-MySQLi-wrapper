<?php

	require("class.DBEngine.php");

	$db = new DBEngine("root", "password", "database");
	// The following will create and execute the statement select * from users where username = test and password = pass
	$db->select("users", array(
					"username" => "test",
					"password" => "pass"
				)
	);

	// Get last array count
	echo $db->get_last_count();

	// Echo the array holding the last result of a select statement
	var_dump($db->result);

	// The following will create and execute the statement insert into users(username, password) values('kolo3', 'lol)
	$db->insert("users", array("username" => "kolo3", "password" => "lol", "code" => "lol"));

	// The following will create and execute the statement update users set password = "kolo3" where username = kolo3
	$db->update("users", array("password" => "kolo3"), array("username" => "kolo3"));

	// The following will create and execute the statement delete from users where username = "KOL"
	$db->delete("users", array("username" => "KOL"), 'and', true);

	// Execute a custom statement
	$db->custom_stmt("select * from users where username = ?", array("don"), true);

?>
