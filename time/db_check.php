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
 * version		1.0
 * date		08.08.2010
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
	$db = @ mysql_connect ( $db_server, $db_user, $db_passwort )
   		or die ( 'Konnte keine Verbindung zur Datenbank \'web246-time\' herstellen' );
	$db_check = @ mysql_select_db ( $db_name ); 
	
	if($db_check) 
		echo "<p>web246-time on www.beo-timing.ch successfully connected<br></p>";
		
	mysql_close($db);  // Logout der Datenbank	
	
	
		/* Datenbankserver - In der Regel die IP */
	$db_server = 'www.beo-timing.ch';

	/* Datenbankname */
	$db_name = 'web246-timetime';

	/* Datenbankuser */
	$db_user = 'web246-timetime';

	/* Datenbankpasswort */
	$db_passwort = 'time09';
         
	/* Erstellt Connect zu Datenbank her */
	$db = @ mysql_connect ( $db_server, $db_user, $db_passwort )
   		or die ( 'Konnte keine Verbindung zur Datenbank \'web246-timetime\' herstellen' );
	$db_check = @ mysql_select_db ( $db_name ); 
	
	if($db_check) 
		echo "<p>web246-timetime on www.beo-timing.ch successfully connected<br></p>";
		
	mysql_close($db);  // Logout der Datenbank	
	
	
	
	
	
	
	
// Ende PHP
?>

</body>
</html>

