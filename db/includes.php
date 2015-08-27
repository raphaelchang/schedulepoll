<?php

function load_db()
{
	global $db;
	require_once('DBMS.php');
	require_once('MySQL.php');
	require_once('Query.php');

	# global variable for our database, access with $GLOBALS['DB'] or db() #
	$db = new Query("mysql", DB_HOST, DB_USERNAME, DB_PASS, DB_NAME);
	$db->Connect();
}

?>