<?php

function db_connect($db) {
	$dbcnx = @mysql_connect('localhost', 'scheduler', '$cheduler321');
	if (!$dbcnx)
  		exit('<p>Unable to connect to the database server at this time.</p>'.mysql_error());

	if (!@mysql_select_db($db))
  		exit('<p>Unable to locate the catalog database at this time.</p>');
	//line used to identify what table the script is connecting to. It is commented out in a live production
	//echo "connected to ".$db."<br />";
	//blank line used in a live production model
	echo " ";
	return $dbcnx;
}


?>
