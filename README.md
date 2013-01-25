PHP MySQLi wrapper
==================

Introduction
------------

This is a simple PHP wrapper that uses MySQLi to generate simple SQL statements and save time doing so. 

It is non persistant (you connect and disconnect when you're finished).

Documentation
-------------

**Constructor**

	/**
	 * 
	 * Create a new DB connection.
	 * 
	 * @param string $username Username of MySQL user
	 * @param string $password Password of MySQL user
	 * @param string $db The DB we will be using
	 * @param string $host Defaults to localhost
	 * @param boolean $suppress Set to true if you want to suppress output errors, default is false 
	 */

*Example*

	$db = new DBEngine("root", "password", "database"); // Errors are shown
	$db = new DBEngine("root", "password", "localhost", true); // Errors are not shown

**Select statement**

	/**
	 * 
	 * Constructs a select statement and writes it into the results
	 * variable.
	 * 
	 * @param string $from The table you are selecting from
	 * @param array $where An array of column names and values for them 
	 * @param string $orderby Order by which parameter (optional)
	 * @param string $limit Limit (optional)
	 * @param boolean $like If true, query will check for likeness 
	 * @param string $operand Default operand is and, you can also use or
	 * 
	 */

*Examples*

	$db->select("users", array(
					"username" => "test",
					"password" => "pass"
				)
	);
	
	// Generates and executes SELECT * FROM users where username = "test" and password = "pass"

	$db->select("users", array("username" => "test", "password" => "pass"), null, null, null, "or");
	
	// Generates and executes SELECT * FROM users where username = "test" or password = "pass"

	$db->select("users", array("username" => "test", "password" => "pass"), null, null, true, "or");
	
	// Generates and executes SELECT * FROM users where username like "%test%" or password like "%pass%"

	$db->get_last_count();
	
	// Returns last count of select statement
	
	$db->result;
	
	// Array of last select statement

**Insert statement documentation**

	/**
	 * 
	 * Insert a new row in the database.
	 * 
	 * @param string $table The name of the table we are inserting to
	 * @param array $vars An array of keys and values, where key is column name and value is the value we
	 * are inserting
	 * 
	 */

*Example*

	$db->insert("users", array("username" => "kolo3", "password" => "lol", "code" => "lol"));

	// Create and execute the statement INSERT INTO users(username, password) values('kolo3', 'lol')

**Update statement**

	/**
	 * 
	 * Update a row in a table.
	 * 
	 * @param string $table Name of the table we are updating
	 * @param array $vals An array of keys and values where key is column name and value is value of column
	 * @param array $where An array of keys and values where key is column name and value is value of column
	 * @param string $operand Operand, defaults to and
	 * @param boolean $like Use like, defaults to false
	 */

*Example*

	$db->update("users", array("password" => "kolo3"), array("username" => "kolo3"));

	// Create and execute the statement UPDATE users SET password = "kolo3" where username = "kolo3"

**Delete statement**

	/**
	 *
	 * Delete row(s) from the database.
	 *
	 * @param string $from The table you are deleting from
	 * @param array $where An array of key and values, where key is the column and value is the value of the
	 * string we are using for comparing
	 * @param string operand Default operand is and
	 * @param boolean like True or false, if set to true all parameters will be tested for likeness
	 * 
	 */

*Example*

	$db->delete("users", array("username" => "KOL"), 'and', true);

	// Create and execute the statement DELETE FROM users where username = "KOL"

**Custom statement**

	/**
	 * 
	 * Execute a custom SQL statement.
	 * 
	 * @param string $query SQL statement query
	 * @param array $parameters Parameters of the query (to replace ?)
	 * @param boolean $select If type of query is select statement, set to true, default is false
	 * 
	 */

*Example*

	$db->custom_stmt("select * from users where username = ?", array("don"), true);
