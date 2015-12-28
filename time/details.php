<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Detailansicht eines Teilnehmers</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<link rel="stylesheet" href="css_color_red.css" type="text/css"/>
</head>
<body>



<?php
/**
 *******************************************************************************
 * file    details.php
 *******************************************************************************
 * brief    Anzeigen der Details eines Teilnehmers
 * 
 * version		1.3
 * date			8.09.2010
 * author		R. Zoss
 *
 * changelog:	- Anzeigen des Jahr bei der Strecke (v1.2)
 * 				- Fehler bei mehreren Strecken gleichzeitig
 * 				- Zus�tzliche Statistik hinzugef�gt
 * 				 		      
 *******************************************************************************
 */

// Verbindung mit der Datenbank herstellen

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
   or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
$db_check = @ mysql_select_db ( $db_name );

// TeilnehmerKey aus der URL-�bergabe speichern
$TeilnehmerKey=$_GET[key];

// Pers�nliche Daten des ausgew�hlten Teilnehmers  aus Datenbank laden
$sql = "SELECT name, vorname, Club, PLZ, Ort, Nationalitaet FROM teilnehmer WHERE TeilnehmerKey=$TeilnehmerKey";
$res = mysql_query($sql);

$nn = mysql_result($res, 0, "name");
$vn = mysql_result($res, 0, "vorname");
$cl = mysql_result($res, 0, "Club");
$pl = mysql_result($res, 0, "PLZ");
$or = mysql_result($res, 0, "Ort");
$na = mysql_result($res, 0, "Nationalitaet");

echo "<p><b>$vn $nn</b><br>$cl<br>$or, $na</p><br>";

?>
<table border="0" width="600">
<tr bgcolor=#6682e4> <td><tablehead>Strecke</tablehead></td><td><tablehead>Datum</tablehead></td><td><tablehead>Fahrzeit</tablehead></td> </tr>

<?php

$sql = "SELECT zeiten.StreckenKey AS StreckenKey, Streckenname, Typ, Jahr, startzeit, fahrzeit 
		FROM strecken, zeiten, streckentyp 
		WHERE strecken.StreckenKey=zeiten.StreckenKey 
		AND strecken.StreckentypKey=streckentyp.StreckentypKey 
		AND zeiten.TeilnehmerKey=$TeilnehmerKey ORDER BY zeiten.StreckenKey DESC, fahrzeit ASC";
//echo $sql;
$res = mysql_query($sql);
echo mysql_error();
$num = mysql_num_rows($res);

$i=0;
while($i<$num){
	echo "<tr><td>";
	$StreckenKey=mysql_result($res, $i, "StreckenKey");
	$sql = "SELECT count(*) AS count FROM zeiten
			WHERE TeilnehmerKey=$TeilnehmerKey AND StreckenKey=$StreckenKey";
	//echo $sql;
	$res2 = mysql_query($sql);
	$count = mysql_result($res2, 0, "count");
	$str = mysql_result($res, $i, "Streckenname");
	$typ = mysql_result($res, $i, "Typ");
	$year = mysql_result($res, $i, "Jahr");
	
	echo "<h2><a href=\"rangliste.php?rennen=$StreckenKey&jahr=$year&teilnehmer=$TeilnehmerKey\">$str ($typ, $year)</a></h2></td><td>";
	$old_i=$i;
	for($i;$i<$count+$old_i;++$i){
		$datum = mysql_result($res, $i, "startzeit");
		echo dateMysql('d.m.Y', $datum);
		if($i!=$count+$old_i-1){ // Zeilenumbruch, ausser bei der letzten Zeile
			echo "<br>";
		}
	}
	echo "</td><td>";
	$i=$old_i;
	for($i;$i<$count+$old_i;++$i){
		$time = mysql_result($res, $i, "fahrzeit");
		echo $time;
		if($i!=$count+$old_i-1){ // Zeilenumbruch, ausser bei der letzten Zeile
			echo "<br>";
		}
	}
	echo "</td></tr>";
}		  
echo "</table>";

?>
<br>
<table border="0" width="600">
<tr bgcolor=#6682e4> <td><tablehead>Jahr</tablehead></td><td><tablehead>Strecken (Teilgenommen / Total)</tablehead></td><td><tablehead>Anzahl Teilnahmen</tablehead></td> </tr>

<?php

$sql="SELECT jahr FROM zeiten,strecken WHERE zeiten.StreckenKey = strecken.StreckenKey AND TeilnehmerKey=$TeilnehmerKey GROUP BY jahr ORDER BY jahr DESC";
$res = mysql_query($sql);
echo mysql_error();
$num = mysql_num_rows($res);
$i=0;
while($i<$num){
	echo "<tr><td>";
	$jahr_stat=mysql_result($res, $i, "jahr");
	echo "$jahr_stat</td><td>";
	
	$sql="SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey AND jahr=$jahr_stat GROUP BY zeiten.StreckenKey) as cnt";
	$res_stat1 = mysql_query($sql);
	$nr_tn = mysql_result($res_stat1, 0, "sum");
	
	$sql="SELECT COUNT(*) as cnt FROM strecken WHERE jahr=$jahr_stat";
	$res_stat1 = mysql_query($sql);
	$nr_total = mysql_result($res_stat1, 0, "cnt");
	//echo $sql;
	echo "$nr_tn / $nr_total";
	
	echo "</td><td>";
	
	$sql="SELECT count(*) as cnt FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey AND jahr=$jahr_stat";
	$res_stat2 = mysql_query($sql);
	$nr = mysql_result($res_stat2, 0, "cnt");
	//echo $sql;
	echo "$nr";
	
	
	$i++;
	echo "</td></tr>";
}

echo "<tr><td><b>Total</b></td><td>";

$sql="SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey GROUP BY zeiten.StreckenKey) as cnt";
$res_stat1 = mysql_query($sql);
$nr_tn = mysql_result($res_stat1, 0, "sum");

$sql="SELECT COUNT(*) as cnt FROM strecken";
$res_stat1 = mysql_query($sql);
$nr_total = mysql_result($res_stat1, 0, "cnt");
//echo $sql;
echo "<b>$nr_tn / $nr_total</b>";
echo "</td><td>";
$sql="SELECT count(*) as cnt FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey";
$res_stat2 = mysql_query($sql);
$nr = mysql_result($res_stat2, 0, "cnt");
//echo $sql;
echo "<b>$nr</b>";
echo "</td></tr>";
echo "</table>";


 
 
 function dateMysql($format, $zeit){
    $jahr=substr($zeit, 0, 4);
    $monat=substr($zeit, 5, 2);
    $tag=substr($zeit, 8, 2);
    $stunde=substr($zeit, 11, 2);
    $minute=substr($zeit, 14, 2);
    $sekunde=substr($zeit, 17, 2);

    $zeit=mktime($stunde, $minute, $sekunde, $monat, $tag, $jahr);

    return date($format, $zeit);
} 
 
?>

</body>
</html>

