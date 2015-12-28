<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Ewige Gesamtrangliste BEO-Timing</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<!--<link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/template_css.css" type="text/css"/><link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/menu.css" type="text/css"/>-->

</head>
<body>




<?php

/**
 *******************************************************************************
 * file    ewiggesrangliste.php
 *******************************************************************************
 * brief    Darstellung der ewigen Gesamtrangliste aller Rennen inkl. verschiedener Filterfunktionen
 * 
 * version		1.0.0
 * date		    30.09.2010	
 * author		R. Zoss
 * 
 * changelog:	- 
 *
 *******************************************************************************
 */
 
function date_mysql2german($date) {
    $d    =    explode("-",$date);
    
    return    sprintf("%02d.%02d.%04d", $d[2], $d[1], $d[0]);
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
	$db = @ mysql_connect ( $db_server, $db_user, $db_passwort )
   		or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
	$db_check = @ mysql_select_db ( $db_name );  
	// Aktuelles Datum holen
	$datum = getdate(time());
	
$datum_str="$datum[year]-$datum[mon]-$datum[mday]";




   $sql = "SELECT count(*) as Yearcnt FROM `strecken` WHERE StreckentypKey=1 AND Enddatum < '$datum_str'";
   //echo $sql;
   $res = mysql_query($sql);
   $race_per_year = mysql_result($res, 0, "Yearcnt");		

   $sql = "SELECT TeilnehmerKey, Name, Vorname, Ort, Jahrgang, Club, SEC_TO_TIME(sum(TIME_TO_SEC(Fahrzeit))) as Totaltime FROM 
		   (SELECT Name, Vorname, Ort, Jahrgang, Club, zeiten.TeilnehmerKey, min(Fahrzeit) as Fahrzeit FROM zeiten,strecken, 
		   teilnehmer WHERE zeiten.StreckenKey = strecken.StreckenKey AND zeiten.TeilnehmerKey = 
		   teilnehmer.TeilnehmerKey AND StreckentypKey=1 AND strecken.Enddatum < '$datum_str' GROUP BY zeiten. TeilnehmerKey, zeiten.StreckenKey) 
		   result GROUP BY TeilnehmerKey ORDER BY Totaltime"; 
		   
   
   //echo $sql;
   $res = mysql_query($sql);
   $num = mysql_num_rows($res);
   

    echo "<h1>Rangliste: \"Ewige Gesamtrangliste\"</h1>";
    echo "<br>" ;
    
    echo "<p>Voraussetzung für das Erscheinen in dieser Rangliste ist die Teilnahme an allen abgeschlossenen $race_per_year Rennen der Kategorie \"Rennrad\" von BEO-Timing.</p>";
    echo "<br>" ;
   //echo "nach dr if ahwisig<br />";
   // Tabellenbeginn
   echo "<table border=\"0\" width=\"660\">";

     // Überschrift

   echo "<tr bgcolor=#6682e4> <td><tablehead>Rang</tablehead></td> <td><tablehead>Name</tablehead></td>";
   echo "<td><tablehead>Vorname</tablehead></td> <td><tablehead>Ort</tablehead></td>";
   echo "<td><tablehead>Jahrgang</tablehead></td> <td><tablehead>Team / Club</tablehead></td>";
   echo "<td><tablehead>Zeit</tablehead></td> <td><tablehead>Rückstand</tablehead></td>";
   echo "</tr>";

  for ($i=0; $i<$num; $i++) //  for ($i=0; $i<$num; $i++)
   {
      //echo "For: $i";
     
      $tk = mysql_result($res, $i, "TeilnehmerKey");
	  $nn = mysql_result($res, $i, "name");
      $vn = mysql_result($res, $i, "vorname");
      $pn = mysql_result($res, $i, "ort");
      $ge = mysql_result($res, $i, "jahrgang");
      $gt = mysql_result($res, $i, "club");
      $gs = mysql_result($res, $i, "Totaltime");
//      echo $tk, $nn, $vn;
      if($i==0) {
		$rang=0;
	  }
      
	  // Abfragen der Anzahl Fahrten
	  $sql = "SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE 
		      strecken.StreckenKey=zeiten.StreckenKey AND TeilnehmerKey = $tk AND StreckentypKey=1 AND strecken.Enddatum < '$datum_str' 
		      GROUP BY zeiten.StreckenKey) as tncnt";
   	  $res_num = mysql_query($sql);
      $nz = mysql_result($res_num, 0, "sum");
      
      //echo "$nz / $race_per_year";
      
      if($nz == $race_per_year) {
			if($rang==0)
				$winnertime=$gs;
			//$gu = timeMysql($gs) - timeMysql($winnertime);
		    $gu = date("H:i:s", timeMysql($gs) - timeMysql($winnertime) - 3600); // (- 1h für Server Zeit)
			if($gu == "00:00:00")
				$gu = "--";
      	    $rang = $rang + 1;
	  		echo "<tr>";
			// Tabellenzeile mit -zellen
			echo "<td>$rang</td> <td><a href=\"details.php?key=$tk\">$nn</a></td> <td><a href=\"details.php?key=$tk\">$vn</a></td>";
			echo "<td>$pn</td> <td align=\"center\">$ge</td> <td>$gt</td> <td align=\"center\">$gs</td> <td align=\"center\">$gu</td></tr>";
			echo "</div>";
	  }
   }
   
   // Tabellenende
   echo "</table>";
   
  
   mysql_close($db);
?>


</body>
</html>



<?php
function dateMysql(/*$format, */$zeit){
	
    $jahr=substr($zeit, 0, 4);
    $monat=substr($zeit, 5, 2);
    $tag=substr($zeit, 8, 2);
    $stunde=substr($zeit, 11, 2);
    $minute=substr($zeit, 14, 2);
    $sekunde=substr($zeit, 17, 2);

    $zeit=mktime($stunde, $minute, $sekunde, $monat, $tag, $jahr);
	
	return $zeit;
    //return date($format, $zeit);
}

function timeMysql(/*$format, */$zeit){

    $jahr=1970;
    $monat=1;
    $tag=1;
    $stunde=substr($zeit, 0, 2);
    $minute=substr($zeit, 3, 2);
    $sekunde=substr($zeit, 6, 2);
    
    
    $zeit=mktime($stunde, $minute, $sekunde);

	return $zeit;
    //return date($format, $zeit);
}






?> 



