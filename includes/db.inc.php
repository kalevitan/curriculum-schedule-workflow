<?php

$dsn = 'mysql:host=localhost;dbname=curschedule';
$username = 'scheduler';
$password = '$cheduler321';

	try {
	    $db = new PDO($dsn, $username, $password);
	} catch (PDOException $e) {
	    $error_message = $e->getMessage();
	    include('errors/database_error.php');
	    exit();
	}

?>
