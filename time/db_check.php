<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
       <title>Test database connection</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php

/**
 *******************************************************************************
 * file    db_check.php
 *******************************************************************************
 * brief    Skript zum Testen der Datenbank anbindung
 * 
 * version		2.0
 * date		04.06.2017
 * author		R. Zoss
 *
 *******************************************************************************
 */

// Begin PHP
	/* Datenbankserver - In der Regel die IP */
	$db_server = 'ricozo6.mysql.db.internal';

	/* Datenbankname */
	$db_name = 'ricozo6_beotimingtime';

	/* Datenbankuser */
	$db_user = 'ricozo6_beotim';

	/* Datenbankpasswort */
	$db_passwort = 'KGloQyRd';
         
	/* Erstellt Connect zu Datenbank her */
	$db = @ mysqli_connect ( $db_server, $db_user, $db_passwort, $db_name )
   		or die ( 'Konnte keine Verbindung zur Datenbank \'web246-time\' herstellen' );
	
	if($db) 
		echo "<p>web246-time on www.beo-timing.ch successfully connected<br></p>";
		
	mysqli_close($db);  // Logout der Datenbank	
	
		
	
	
// Ende PHP
?>

</body>
</html>

