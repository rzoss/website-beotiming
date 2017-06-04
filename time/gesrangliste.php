<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Gesamtrangliste BEO-Timing</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<!--<link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/template_css.css" type="text/css"/><link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/menu.css" type="text/css"/>-->

</head>
<body>




<?php

/**
 *******************************************************************************

 * file    gesrangliste.php
 *******************************************************************************
 * brief    Darstellung der Gesamtrangliste aller Rennen inkl. verschiedener Filterfunktionen
 * 
 * version		2.0.0
 * date		    04.06.2017	
 * author		R. Zoss
 * 
 * changelog:	- Aktualisierungen für PHP 7.x
 *
 *******************************************************************************
 */
 
function date_mysql2german($date) {
    $d    =    explode("-",$date);
    
    return    sprintf("%02d.%02d.%04d", $d[2], $d[1], $d[0]);
}

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
	$db = @ mysqli_connect ( $db_server, $db_user, $db_passwort, $db_name  )
   		or die ( 'Konnte keine Verbindung zur Datenbank herstellen' ); 
	// Aktuelles Datum holen
	$datum = getdate(time());
	



	$Year=$_GET['year'];

	$datum_str="$datum[year]-$datum[mon]-$datum[mday]";
   $sql = "SELECT count(*) as Yearcnt FROM `strecken` WHERE Jahr = $Year AND StreckentypKey=1 AND Enddatum < '$datum_str'";

   $res = mysqli_query($db, $sql);
   $race_per_year = mysqli_result($res, 0, "Yearcnt");		

   $sql = "SELECT TeilnehmerKey, Name, Vorname, Ort, Jahrgang, Club, SEC_TO_TIME(sum(TIME_TO_SEC(Fahrzeit))) as Totaltime FROM 
		   (SELECT Name, Vorname, Ort, Jahrgang, Club, zeiten.TeilnehmerKey, min(Fahrzeit) as Fahrzeit FROM zeiten,strecken, 
		   teilnehmer WHERE zeiten.StreckenKey = strecken.StreckenKey AND zeiten.TeilnehmerKey = 
		   teilnehmer.TeilnehmerKey AND strecken.jahr = $Year AND StreckentypKey=1 AND strecken.Enddatum < '$datum_str' GROUP BY zeiten.TeilnehmerKey, zeiten.StreckenKey) 
		   result GROUP BY TeilnehmerKey ORDER BY Totaltime"; 
		   
   
   //echo $sql;
   $res = mysqli_query($db, $sql);
   $num = mysqli_num_rows($res);
   

    echo "<h1>Rangliste: \"Gesamtrangliste Rennrad\" ($Year)</h1>";
    echo "<br>" ;

    if($datum['year']==$Year)
		echo "<p>Voraussetzung für das Erscheinen in dieser Rangliste ist die Teilnahme an den bisher abgeschlossenen $race_per_year Rennen der Kategorie \"Rennrad\" von BEO-Timing im Jahr $Year.</p>";
	else
	    echo "<p>Voraussetzung für das Erscheinen in dieser Rangliste ist die Teilnahme an allen $race_per_year Rennen der Kategorie \"Rennrad\" von BEO-Timing im Jahr $Year.</p>";
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
     
      $tk = mysqli_result($res, $i, "TeilnehmerKey");
	  $nn = mysqli_result($res, $i, "Name");
      $vn = mysqli_result($res, $i, "Vorname");
      $pn = mysqli_result($res, $i, "Ort");
      $ge = mysqli_result($res, $i, "Jahrgang");
      $gt = mysqli_result($res, $i, "Club");
      $gs = mysqli_result($res, $i, "Totaltime");
//      echo $tk, $nn, $vn;
      if($i==0) {
		$rang=0;
	  }

      //echo "$tk, $nn, $vn, $pn, $ge, $gt, $gs<br>";
	  // Abfragen der Anzahl Fahrten
	  $sql = "SELECT COUNT(tmp) as sum FROM (SELECT count(*) as tmp FROM zeiten,strecken WHERE 
		      strecken.StreckenKey=zeiten.StreckenKey AND strecken.Jahr = $Year AND TeilnehmerKey = $tk AND StreckentypKey=1 AND strecken.Enddatum < '$datum_str' 
		      GROUP BY zeiten.StreckenKey) as tncnt";
   	  $res_num = mysqli_query($db, $sql);
      $nz = mysqli_result($res_num, 0, "sum");
      
      //echo "$nz / $race_per_year";
      
      if($nz == $race_per_year && $nz > 1) {
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
   
  
   mysqli_close($db);
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



