<?php

/**
 * 
 * A class for MySQL data management using MySQLi.
 * 
 * @author Boštjan Cigan (http://bostjan.gets-it.net; http://zerocool.is-a-geek.net)
 * @license GPL v2.0
 *
 */

class DBEngine {
	
	private $host;
	private $username;
	private $password;
	private $db;
	private $established = false;
	private $suppress = false;
	
	private $connection;
	
	private $last_error;
	private $result;
	private $last_count;
	
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
	public function __construct($username, $password, $db, $host = "localhost", $suppress=false) {
		
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->db = $db;
		$this->suppress = $suppress;
		
		$this->init_connection();
		
	}
	
	/**
	 * 
	 * Initialize connection
	 * 
	 */
	private function init_connection() {
		
		$db = new mysqli($this->host, $this->username, $this->password, $this->db);
		if($db->connect_errno > 0) {
			$this->last_error = "Unable to connect to database. [ ".$db->connect_error." ]";
		}
		else {
			$this->connection = $db;
			$this->established = true;
		}
	
	}
	
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
	public function select($from, $where='', $orderby='', $limit='', $like=false, $operand='and') {
	
		if($this->established) {
		
			$query = "select * from {$from}";
			
			if(is_array($where)) {
				$query .= " where ";
				$count = 0;
				foreach($where as $key => $value) {
					if($count == 0) {
						if($like) {
							$query .= "{$key} like concat('%', ?, '%') ";
						}
						else {
							$query .= "{$key} = ? ";
						}
					}
					else {
						if($like) {
							$query .= "{$operand} {$key} like concat('%', ?, '%') "; 
						}
						else {
							$query .= "{$operand} {$key} = ?";
						}
					} 
					$count++;
				}
			}
			
			if(strlen($orderby) > 0) {
				$query .= " order by {$orderby}";
			}
			
			if(strlen($limit) > 0) {
				$query .= " limit {$limit}";
			}
			
			if(is_array($where)) {
				$bind_string = $this->get_bind_string($where);
				$bind_parameters = $this->get_bind_parameters($where);
	
				$stmt = $this->connection->prepare($query);
				$merge = array_merge($bind_string, $bind_parameters);
			
				$ref_array = array();
				foreach($merge as $key => $value) {
					$ref_array[$key] = &$merge[$key];
				}
				call_user_func_array(array($stmt, 'bind_param'), $ref_array);
			} else {
				$stmt = $this->connection->prepare($query);
			}
			
			$stmt->execute();
			$results = $this->get_array($stmt);
			$this->last_count = sizeof($results);
			$this->result = $results;
			$this->report_error();
			$stmt->free_result();
			$stmt->close();
				
		}
		else {
			$this->last_count = -1;
			$this->last_error = "No database connection established.";
		}
				
	}

	/**
	 * 
	 * Insert a new row in the database.
	 * 
	 * @param string $table The name of the table we are inserting to
	 * @param array $vars An array of keys and values, where key is column name and value is the value we
	 * are inserting
	 * 
	 */
	public function insert($table, $vars) {

		if($this->established) {
		
			$query = "insert into {$table} ";
			$cols = "(";
			$count = 0;
			$params = "";
			foreach($vars as $key => $value) {
				if($count == 0) {
					$cols .= "{$key}";
					$params .= "?";
				}
				else {
					$cols .= ",{$key}";
					$params .= ",?";
				}
				$count++;
			}
			$cols .= ") ";
			$query .= $cols;
			$query .= "values(";
			$query .= $params;
			$query .= ")";
	
			$bind_string = $this->get_bind_string($vars);
			$bind_parameters = $this->get_bind_parameters($vars);
			
			$stmt = $this->connection->prepare($query);
			$merge = array_merge($bind_string, $bind_parameters);
			
			$ref_array = array();
			foreach($merge as $key => $value) {
				$ref_array[$key] = &$merge[$key];
			}
			call_user_func_array(array($stmt, 'bind_param'), $ref_array);
			
			$stmt->execute();
			$this->last_count = $this->connection->affected_rows;
			$this->report_error();
			$stmt->free_result();
			$stmt->close();
			
		}
		else {
			$this->last_count = -1;
			$this->last_error = "No database connection established.";
		}
		
	}
	
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
	public function update($table, $vals, $where, $operand='and', $like=false) {
		
		if($this->established) {
			
			$query = "update {$table} set ";
			$count = 0;
			foreach($vals as $key => $value) {
				if($count == 0) {
					$query .= "{$key} = ?";
				}
				else {
					$query .= ", {$key} = ?";
				}
				$count++;
			} 
			
			$count = 0;
			foreach($where as $key => $value) {
				if($count == 0) {
					if($like) {
						$query .= " where {$key} like concat('%', ?, '%')";
					}
					else {
						$query .= " where {$key} = ?";
					}
				}
				else {
					if($like) {
						$query .= " {$operand} {$key} like concat('%', ?, '%')";	
					}
					else {
						$query .= " {$operand} {$key} = ?";
					}
				}
				$count++;
			}
			
			$bind_string = $this->get_bind_string($vals, $where);
			$bind_parameters = $this->get_bind_parameters($vals, $where);
			
			$stmt = $this->connection->prepare($query);
			$merge = array_merge($bind_string, $bind_parameters);
			
			$ref_array = array();
			foreach($merge as $key => $value) {
				$ref_array[$key] = &$merge[$key];
			}
			call_user_func_array(array($stmt, 'bind_param'), $ref_array);
			
			$stmt->execute();
			$this->last_count = $this->connection->affected_rows;
			$this->report_error();
			$stmt->free_result();
			$stmt->close();
		
		}
		else {
			$this->last_count = -1;
			$this->last_error = "No database connection established.";
		}
		
	}
	
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
	public function delete($from, $where='', $operand='and', $like=false) {
	
		if($this->established) {
		
			$query = "delete from {$from} ";
		
			if(is_array($where)) {
				$query .= "where ";
				$count = 0;
					
				foreach($where as $key => $value) {
					if($count == 0) {
						if($like) {
							$query .= "{$key} like concat('%', ?, '%') ";
						}
						else {
							$query .= "{$key} = ? ";
						}
					}
					else {
						if($like) {
							$query .= "{$operand} {$key} like concat('%', ?, '%') ";
						}
						else {
							$query .= "{$operand} {$key} = ? ";
						}
					}
					$count++;
				}
			}
		
			$bind_string = $this->get_bind_string($where);
			$bind_parameters = $this->get_bind_parameters($where);
		
			$stmt = $this->connection->prepare($query);
			$merge = array_merge($bind_string, $bind_parameters);
		
			$ref_array = array();
			foreach($merge as $key => $value) {
				$ref_array[$key] = &$merge[$key];
			}
			call_user_func_array(array($stmt, 'bind_param'), $ref_array);
		
			$stmt->execute();
			$this->last_count = $this->connection->affected_rows;
			$this->report_error();
			$stmt->free_result();
			$stmt->close();
			
		}
		else {
			$this->last_count = -1;
			$this->last_error = "No database connection established.";
		}
	
	}

	/**
	 * 
	 * Execute a custom SQL statement.
	 * 
	 * @param string $query SQL statement query
	 * @param array $parameters Parameters of the query (to replace ?)
	 * @param boolean $select If type of query is select statement, set to true, default is false
	 * 
	 */
	public function custom_stmt($query, $parameters='', $select=false) {
		
		$results = null;
		
		if($this->established) {
			
			if (is_array($parameters)){
				$bind_string = $this->get_bind_string($parameters);
				$bind_parameters = $this->get_bind_parameters($parameters);
			
				$stmt = $this->connection->prepare($query);
				$merge = array_merge($bind_string, $bind_parameters);
				
				$ref_array = array();
				foreach($merge as $key => $value) {
					$ref_array[$key] = &$merge[$key];
				}
				call_user_func_array(array($stmt, 'bind_param'), $ref_array);
			} else {
				$stmt = $this->connection->prepare($query);
			}
			
			$stmt->execute();
			if($select) {
				$this->result = $this->get_array($stmt);
				$this->last_count = sizeof($results);
			}
			$this->report_error();
			$stmt->free_result();
			$stmt->close();
			
		}
		
	}
		
	/**
	 *
	 * Creates an array of bind parameters.
	 * 
	 * @param Array $arr Array with input $key => $value, where key is the SQL column name and value is 
	 * the value of the column
	 * @return Array $params
	 */
	private function get_bind_parameters($arr, $arr2 = null, $custom = false) {

		$params = array();
		foreach($arr as $key => $value) {
			$params[] = $value;
		}
		if(!is_null($arr2)) {
			foreach($arr2 as $key => $value) {
				$params[] = $value;
			}
		}
		
		return $params;
		
	}
	
	/**
	 * 
	 * Accepts a key value array and returns a new array as key value where key
	 * is the value of the previous array and value is the parameter type
	 * 
	 * @param ArrayObject $arr Array of keys and values
	 * @param ArrayObject $arr2 Optional if binding two arrays
	 * @return Array $bind_array A bind params string
	 */
	private function get_bind_string($arr, $arr2 = null) {
		
		$bind_array = "";
		foreach($arr as $key => $value) {
			$type = is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
			$bind_array .= $type;
		}
		if(!is_null($arr2)) {
			foreach($arr2 as $key => $value) {
				$type = is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
				$bind_array .= $type;
			}
		}

		return array($bind_array);
		
	}
	
	/**
	 * 
	 * Accept a statement and returns an array of returned values from the database.
	 * 
	 * @param Statement $stmt
	 * @return ArrayObject
	 */
	private function get_array($stmt) {

		$meta = $stmt->result_metadata();
		$parameters = array();
		$results = array();
		
		while($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}
		
		call_user_func_array(array($stmt, 'bind_result'), $parameters);
		
		while($stmt->fetch()) {
			$x = array();
			foreach($row as $key => $val) {
				$x[$key] = $val;
			}
			$results[] = $x;
		}
		
		return $results;
		
	}	

	/**
	 * 
	 * Writes and outputs last error if errors are not suppressed.
	 * 
	 */
	private function report_error() {
		if($this->last_count < 1) {
			$this->last_error = $this->connection->error;
			if(!$this->suppress) {
				echo $this->last_error;
			}
		}
	}
	
	/**
	 * 
	 * Returns an array of the last executed select statement.
	 * 
	 * @return array An array of the last executed statement and the results of it
	 */
	public function get_result() {
		return $this->result;
	}
	
	/**
	 * 
	 * Returns last count value.
	 * 
 	 * @return int
 	 * 
	 */
	public function get_last_count() {
		return $this->last_count;
	}
	
	/**
	 * 
	 * Returns last error
	 * @return string
	 */
	public function get_last_error() {
		return $this->last_error;
	}
	
	/**
	 * 
	 * Close the MySQL connection
	 * 
	 */
	public function close_connection() {
		$this->connection->close();
	}
	
}