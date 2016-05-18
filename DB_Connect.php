<?php
//error_reporting(E_ALL ^ E_DEPRECATED);
class DB_Connect{	
	//Constructor
	function __construct(){
		
	}
	
	//Destructor
	function __destruct(){
		//this->close();
	}
	
	//Connecting to databse
	public function connect(){
		require_once 'config.php';
		//connecting to mysql
		$con = @mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
		//selecting databse
		mysql_select_db(DB_DATABASE);
		
		//return databse handler
		return $con;
	}
	
	//Closing databse connection
	public function close(){
		mysql_close();
	}
}
?>