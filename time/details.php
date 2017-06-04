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
 * version		2.0.0
 * date			04.06.2017
 * author		R. Zoss
 *
 * changelog:	- Aktualisierungen für PHP 7.x
 * 				 		      
 *******************************************************************************
 */

function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}
 
 
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
$db = @ mysqli_connect ( $db_server, $db_user, $db_passwort, $db_name )
   or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );


// TeilnehmerKey aus der URL-übergabe speichern
$TeilnehmerKey=$_GET[key];

// Persönliche Daten des ausgewählten Teilnehmers  aus Datenbank laden
$sql = "SELECT name, vorname, Club, PLZ, Ort, Nationalitaet FROM teilnehmer WHERE TeilnehmerKey=$TeilnehmerKey";
$res = mysqli_query($db, $sql);

$nn = mysqli_result($res, 0, "name");
$vn = mysqli_result($res, 0, "vorname");
$cl = mysqli_result($res, 0, "Club");
$pl = mysqli_result($res, 0, "PLZ");
$or = mysqli_result($res, 0, "Ort");
$na = mysqli_result($res, 0, "Nationalitaet");

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
$res = mysqli_query($db, $sql);
echo mysqli_error($db);
$num = mysqli_num_rows($res);

$i=0;
while($i<$num){
	echo "<tr><td>";
	$StreckenKey=mysqli_result($res, $i, "StreckenKey");
	$sql = "SELECT count(*) AS count FROM zeiten
			WHERE TeilnehmerKey=$TeilnehmerKey AND StreckenKey=$StreckenKey";
	//echo $sql;
	$res2 = mysqli_query($db, $sql);
	$count = mysqli_result($res2, 0, "count");
	$str = mysqli_result($res, $i, "Streckenname");
	$typ = mysqli_result($res, $i, "Typ");
	$year = mysqli_result($res, $i, "Jahr");
	
	echo "<h2><a href=\"rangliste.php?rennen=$StreckenKey&jahr=$year&teilnehmer=$TeilnehmerKey\">$str ($typ, $year)</a></h2></td><td>";
	$old_i=$i;
	for($i;$i<$count+$old_i;++$i){
		$datum = mysqli_result($res, $i, "startzeit");
		echo dateMysql('d.m.Y', $datum);
		if($i!=$count+$old_i-1){ // Zeilenumbruch, ausser bei der letzten Zeile
			echo "<br>";
		}
	}
	echo "</td><td>";
	$i=$old_i;
	for($i;$i<$count+$old_i;++$i){
		$time = mysqli_result($res, $i, "fahrzeit");
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
$res = mysqli_query($db, $sql);
echo mysqli_error($db);
$num = mysqli_num_rows($res);
$i=0;
while($i<$num){
	echo "<tr><td>";
	$jahr_stat=mysqli_result($res, $i, "jahr");
	echo "$jahr_stat</td><td>";
	
	$sql="SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey AND jahr=$jahr_stat GROUP BY zeiten.StreckenKey) as cnt";
	$res_stat1 = mysqli_query($db, $sql);
	$nr_tn = mysqli_result($res_stat1, 0, "sum");
	
	$sql="SELECT COUNT(*) as cnt FROM strecken WHERE jahr=$jahr_stat";
	$res_stat1 = mysqli_query($db, $sql);
	$nr_total = mysqli_result($res_stat1, 0, "cnt");
	//echo $sql;
	echo "$nr_tn / $nr_total";
	
	echo "</td><td>";
	
	$sql="SELECT count(*) as cnt FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey AND jahr=$jahr_stat";
	$res_stat2 = mysqli_query($db, $sql);
	$nr = mysqli_result($res_stat2, 0, "cnt");
	//echo $sql;
	echo "$nr";
	
	
	$i++;
	echo "</td></tr>";
}

echo "<tr><td><b>Total</b></td><td>";

$sql="SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey GROUP BY zeiten.StreckenKey) as cnt";
$res_stat1 = mysqli_query($db, $sql);
$nr_tn = mysqli_result($res_stat1, 0, "sum");

$sql="SELECT COUNT(*) as cnt FROM strecken";
$res_stat1 = mysqli_query($db, $sql);
$nr_total = mysqli_result($res_stat1, 0, "cnt");
//echo $sql;
echo "<b>$nr_tn / $nr_total</b>";
echo "</td><td>";
$sql="SELECT count(*) as cnt FROM zeiten,strecken WHERE strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $TeilnehmerKey";
$res_stat2 = mysqli_query($db, $sql);
$nr = mysqli_result($res_stat2, 0, "cnt");
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

