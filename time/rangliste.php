﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Rangliste BEO-Timing</title>
<link rel="stylesheet" type="text/css" href="style.css" />



<!--<link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/template_css.css" type="text/css"/><link rel="stylesheet" href="http://www.beo-timing.ch/Joomla_1_0_x/templates/t2w_soft_1.0/css/menu.css" type="text/css"/>-->

</head>
<body>




<?php

/**
 *******************************************************************************
 * file    rangliste.php
 *******************************************************************************
 * brief    Darstellung der Rangliste aller Rennen inkl. verschiedener Filterfunktionen
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
	$db = @ mysqli_connect ( $db_server, $db_user, $db_passwort, $db_name )
   		or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
		// Aktuelles Datum holen
	$datum = getdate(time());
	$highlight = "";
	if(($_POST!=NULL && $_POST['rennen']!=NULL) || !empty($_GET))
	{
		if (!empty($_GET))
		{
			$rennenPost = $_GET[rennen];
			$jahrPost = $_GET[jahr];
			$highlight = $_GET[teilnehmer];
		}
		else
		{
			$rennenPost = $_POST['rennen'];
			$jahrPost = $_POST['jahr'];
			
		}
		
		
		//$personPost = $_POST['person'];
   	 	//$geschlechtPost = $_POST['geschlecht'];
		// Personensuche für SQL Query vorbereiten (LIKE)
		//$personPost="$personPost%";
		// ausgewählte Kategorie vorbereiten
		//$kategoriePost=$_POST['kategorie'];
		
		// Beide Geschlechter
        $geschlechtPost="%";
		// Personensuche
		$personPost="%";
		// Alle Kategorien
		$kategoriePost="%";
   	 			
		$StreckenKey=$rennenPost;
		

		
		// Ist der aktuelle StreckenKey für das gewählte Jahr gültig?
		// Falls nicht, wurde ein anderes Jahr ausgewählt
		$res = mysqli_query($db, "SELECT count(*) AS existiert 
					FROM strecken WHERE jahr = '$jahrPost' AND StreckenKey=$StreckenKey ORDER BY Enddatum ASC");
		$exists = mysqli_result($res, 0, "existiert");		
		// erstes Rennen wählen, falls nicht das aktuelle Jahr gewählt wurde
		if($exists==0 && $jahrPost!=$datum[year]){
			$res = mysqli_query($db, "SELECT StreckenKey " .
		 					   "FROM strecken WHERE jahr = '$jahrPost' ORDER BY Enddatum ASC");
			$StreckenKey = mysqli_result($res, 0, "StreckenKey");
		}else if($exists==0){
			// Name des aktuellen Rennens herausfinden um diesen in der ComboBox auszuwählen
		 	$datum_str="$datum[year]-$datum[mon]-$datum[mday]";
		 	$res = mysqli_query($db, "SELECT Streckenname, StreckenKey " .
		 					   "FROM strecken WHERE Enddatum >= '$datum_str' AND " .
		 					   "Startdatum <= '$datum_str' AND jahr = '$jahrPost' ORDER BY Enddatum ASC");
		 	if(mysqli_num_rows($res)!=0){
		 		// Name des Rennens speichern, falls eines im Moment läuft
		 		$rennenPost = mysqli_result($res, 0, "Streckenname");
		 		$StreckenKey = mysqli_result($res, 0, "StreckenKey");
		 	}else{
		 		// Letztes, abgeschlossenes Rennen wählen, falls keines läuft
		 		$res = mysqli_query($db, "SELECT Streckenname, StreckenKey " .
		 					   "FROM strecken WHERE Enddatum < '$datum_str' ORDER BY Enddatum DESC");
		 		$rennenPost = mysqli_result($res, 0, "Streckenname");
		 		$StreckenKey = mysqli_result($res, 0, "StreckenKey");
		 	}
		}else{
			// nichts tun, da kein neues Jahr gewählt wurde
		}
		
		
    }else{
 		 
		 // Name des aktuellen Rennens herausfinden um diesen in der ComboBox auszuwählen
		 // Prüfen ob Rennen im aktuellen Jahr vorhanden sind. Verwende 31-12-<letztes Jahr> andernfalls. 
		 $res = mysqli_query($db, "SELECT count(*) AS existiert 
					FROM strecken WHERE jahr = '$datum[year]'");
		 $exists = mysqli_result($res, 0, "existiert");	
		 if($exists==0){
			 $res = mysqli_query($db, "SELECT MAX(jahr) AS jahr FROM strecken");
			 $datum_jahr = mysqli_result($res, 0, "jahr");
			 $datum_str="$datum_jahr-12-31";
		 }else{
			 $datum_jahr="$datum[year]";
			 $datum_str="$datum_jahr-$datum[mon]-$datum[mday]";
		 }
		 
		 $res = mysqli_query($db, "SELECT Streckenname, StreckenKey " .
		 					   "FROM strecken WHERE Enddatum > '$datum_str' AND " .
		 					   "Startdatum < '$datum_str' AND jahr = '$datum_jahr' ORDER BY Enddatum ASC");
		 if(mysqli_num_rows($res)!=0){
		 	// Name des Rennens speichern, falls eines im Moment läuft
		 	$rennenPost = mysqli_result($res, 0, "Streckenname");
		 	$StreckenKey = mysqli_result($res, 0, "StreckenKey");
		 }else{
		 	// Letztes, abgeschlossenes Rennen wählen, falls keines läuft
		 	$res = mysqli_query($db, "SELECT Streckenname, StreckenKey " .
		 					   "FROM strecken WHERE Enddatum < '$datum_str' AND Jahr = '$datum_jahr' ORDER BY Enddatum DESC");
			if(mysqli_num_rows($res)!=0){
				$rennenPost = mysqli_result($res, 0, "Streckenname");
				$StreckenKey = mysqli_result($res, 0, "StreckenKey");
			}else{
				$res = mysqli_query($db, "SELECT Streckenname, StreckenKey " .
		 					   "FROM strecken WHERE Enddatum > '$datum_str' AND Jahr = '$datum_jahr' ORDER BY Enddatum ASC");

				$rennenPost = mysqli_result($res, 0, "Streckenname");
				$StreckenKey = mysqli_result($res, 0, "StreckenKey");
			}
			
		 }
		 // Aktuelles Jahr speichern
		 $jahrPost=$datum_jahr;
         // Beide Geschlechter
         $geschlechtPost="%";
		 // Personensuche
		 $personPost="%";
		 $kategoriePost="%";
		

		 	
    }
		
	//echo "$rennenPost   $personPost   $geschlechtPost    $jahrPost     $StreckenKey";

	// Logo Anzeigen

	$res = mysqli_query($db, "SELECT bild, text, breite, beschriftung, url " .
						"FROM logo WHERE strecke = '$StreckenKey'");
						
	if ($res) {
    	$logo_name = mysqli_result($res, 0, "bild");
		$logo_text = mysqli_result($res, 0, "text");
		$logo_breite = mysqli_result($res, 0, "breite");
		$logo_beschriftung = mysqli_result($res, 0, "beschriftung");
		$logo_url = mysqli_result($res, 0, "url");	
	
		echo "<p>$logo_text</p>";
		if($logo_url == ""){
			echo "<p align=\"center\"><img src=\"logo/$logo_name\" width=\"$logo_breite\" alt=\"$logo_beschriftung\"></p>";
		} else {
			echo "<p align=\"center\"><a href=\"$logo_url\" title=\"$logo_beschriftung\" target=\"_blank\">".
				 "<img src=\"logo/$logo_name\" width=\"$logo_breite\" alt=\"$logo_beschriftung\"></a></p>";
		}
	}
	
?>



<form action="rangliste.php" method="post">

<table border="0" width="660">
<tr bgcolor=#6682e4> <td><tablehead>Rennen</tablehead></td><td><tablehead>Jahr</tablehead></td>
<tr>
<td>
    <select name="rennen">
     <?php
     
		 $sql = "SELECT Streckenname, StreckenKey, Typ, Startdatum, Enddatum " .
		 	    "FROM strecken, streckentyp WHERE jahr='$jahrPost' " .
                "AND strecken.StreckentypKey=streckentyp.StreckentypKey ORDER BY Enddatum ASC";
         

		 $res = mysqli_query($db, $sql);
                                 
         $num = mysqli_num_rows($res);


         for ($i=0; $i<$num; $i++){
         	$name = mysqli_result($res, $i, "Streckenname");
         	$nr = mysqli_result($res, $i, "StreckenKey");
         	$typ = mysqli_result($res, $i, "Typ");
			$startdate = mysqli_result($res, $i, "Startdatum");
			$startdate=date_mysql2german($startdate);
			$enddate = mysqli_result($res, $i, "Enddatum");
			$enddate=date_mysql2german($enddate);
            if($nr==$StreckenKey){
                echo "<option value=$nr selected=\"selected\">$name, $typ ($startdate - $enddate)</option>";
                $streckenname_str="$name, $typ";
            }else{
                echo "<option value=$nr>$name, $typ ($startdate - $enddate)</option>";
            }
         }
		 echo "</td><td>";
		 echo "<select name=\"jahr\">";
     
          $res = mysqli_query($db, "SELECT strecken.Jahr
                                 FROM strecken
                                 GROUP BY strecken.Jahr");
          $num = mysqli_num_rows($res);
		  // ComboBox mit den vorhandenen Jahren auffüllen und das aktuelle Jahr wählen
          for ($i=0; $i<$num; $i++){
          	$nn = mysqli_result($res, $i, "Jahr");
            if($nn==$jahrPost){
                echo "<option value=$nn selected=\"selected\">$nn</option>";
            }else{
                echo "<option value=$nn>$nn</option>";
            }
          }
         ?>		
     
 </td></tr>
</table>
<input type="submit" value=" Go ">
<hr color = #6682e4>
</form>


<?php
   $sql = "SELECT count(*) AS anzahl FROM zeiten WHERE StreckenKey= $StreckenKey";
   $res = mysqli_query($db, $sql);
   $numzeiten = mysqli_result($res, 0, "anzahl");		


   $sql = "SELECT teilnehmer.TeilnehmerKey, teilnehmer.name, teilnehmer.vorname, teilnehmer.ort, 
    	 teilnehmer.jahrgang,teilnehmer.club, min(zeiten.fahrzeit)AS fahrzeit, 
    	 TIMEDIFF(min(zeiten.fahrzeit),(SELECT min(zeiten.fahrzeit) FROM zeiten 
		 WHERE StreckenKey = $StreckenKey)) AS rueckstand
    	 FROM strecken, zeiten, teilnehmer 
    	 WHERE zeiten.StreckenKey = strecken.StreckenKey 
   	 	 AND zeiten.TeilnehmerKey = teilnehmer.TeilnehmerKey 
   		 AND strecken.StreckenKey = $StreckenKey 
   		 AND teilnehmer.Geschlecht LIKE '$geschlechtPost'  
    	 AND (teilnehmer.Name LIKE '$personPost' OR teilnehmer.Vorname LIKE '$personPost' )
   		 AND zeiten.kategorie LIKE '$kategoriePost' 
   	 	 GROUP BY teilnehmer.TeilnehmerKey
    	 ORDER BY min(zeiten.Fahrzeit), teilnehmer.jahrgang"; 
   //echo $sql;
   $res = mysqli_query($db, $sql);
   $num = mysqli_num_rows($res);



   if($rennenPost=="0"){
   echo "Bitte wählen Sie eine Rennstrecke aus.";
   }else{

    echo "<h1>Rangliste: \"$streckenname_str\" ($jahrPost)</h1>";
    echo "<br>" ;
    echo "<p>$num Teilnehmer mit $numzeiten gemessenen Zeiten</p>";
   //echo "nach dr if ahwisig<br />";
   // Tabellenbeginn
   echo "<table border=\"0\" width=\"660\">";

   // Definition der Farben für das Hervorheben der letzten Zeiten
   $color = array('#FFCC00', '#FFD42A', '#FFDD55', '#FFE680', '#FFEEAA', '#FFF6D5', '#FFFCE5');	// Gelb
//   $color = array('#D45500','#FF6600', '#FF7F2A', '#FF9955', '#FFB380', '#FFCCAA', '#FFE6D5');	// Orange
//   $color = array('#0055D4','#0066FF', '#2A7FFF', '#5599FF', '#80B3FF', '#AACCFF', '#D5E5FF');	// Blau

   // Überschrift

   echo "<tr bgcolor=#6682e4> <td><tablehead>Rang</tablehead></td> <td><tablehead>Name</tablehead></td>";
   echo "<td><tablehead>Vorname</tablehead></td> <td><tablehead>Ort</tablehead></td>";
   echo "<td><tablehead>Jahrgang</tablehead></td> <td><tablehead>Team / Club</tablehead></td>";
   echo "<td><tablehead>Zeit</tablehead></td> <td><tablehead>Rückstand</tablehead></td>";
   echo "<td><tablehead>Tln.</tablehead></td></tr>";

  for ($i=0; $i<$num; $i++)
   {
      $tk = mysqli_result($res, $i, "TeilnehmerKey");
	  $nn = mysqli_result($res, $i, "name");
      $vn = mysqli_result($res, $i, "vorname");
      $pn = mysqli_result($res, $i, "ort");
      $ge = mysqli_result($res, $i, "jahrgang");
      $gt = mysqli_result($res, $i, "club");
      $gs = mysqli_result($res, $i, "fahrzeit");
      $gu = mysqli_result($res, $i, "rueckstand");
      $lf = $i + 1;
	  // Abfragen der Anzahl Fahrten
	  $sql = "SELECT count(*) AS anzahl FROM zeiten WHERE StreckenKey= $StreckenKey AND TeilnehmerKey= $tk";
   	  $res_num = mysqli_query($db, $sql);
      $nz = mysqli_result($res_num, 0, "anzahl");		
	   // Abfragen der Anzahl Fahrten insgesamt
	  //$sql = "SELECT count(*) AS anzahl FROM zeiten WHERE TeilnehmerKey= $tk";
   	  //$res_num = mysqli_query($db, $sql);
      //$gz = mysqli_result($res_num, 0, "anzahl");	
	  
	  // Entscheid für die Farbe der Markierung
	  
	  $sql = "SELECT Endzeit FROM zeiten WHERE StreckenKey= $StreckenKey AND TeilnehmerKey= $tk ORDER BY Fahrzeit";
   	  $res_num = mysqli_query($db, $sql);
          
	  $ez = @mysqli_result($res_num, 0, "Endzeit"); 
	  // Berechnen der Differenz, inkl. Berücksichtigung der Zeitverschiebung des Servers von 1h
	  $ez = (time()+3600-dateMysql($ez))/(3600*24);
	  
	//echo "$ez";

	  echo "<div class=\"a\">";
	  if($highlight==$tk)
			echo "<tr bgcolor=#F78181>";
      else if($ez > 0 && $ez < 7)
	  		echo "<tr bgcolor=".$color[floor($ez)].">";
	  else
	  		echo "<tr>";
	  // Tabellenzeile mit -zellen
	  
	  if($nn=="(noch")
	  {
		  // kein link für (noch nicht ausgewertet)
		  echo "<td>$lf</td> <td>$nn</a></td> <td>$vn</a></td>";
		  echo "<td>$pn</td> <td align=\"center\" >$ge</td> <td>$gt</td> <td align=\"center\" >$gs</td> <td align=\"center\" >$gu</td> <td align=\"center\" >$nz</td></tr>";
	  }
	  else if($gu == "00:00:00")
	  {
		   // kein Rückstand beim ersten 
		  echo "<td>$lf</td> <td><a href=\"details.php?key=$tk\">$nn</a></td> <td><a href=\"details.php?key=$tk\">$vn</a></td>";
		  echo "<td>$pn</td> <td align=\"center\" >$ge</td> <td>$gt</td> <td align=\"center\" >$gs</td> <td align=\"center\" >--</td> <td align=\"center\" >$nz</td></tr>";	 
	  }
	  else
	  {
		  echo "<td>$lf</td> <td><a href=\"details.php?key=$tk\">$nn</a></td> <td><a href=\"details.php?key=$tk\">$vn</a></td>";
		  echo "<td>$pn</td> <td align=\"center\" >$ge</td> <td>$gt</td> <td align=\"center\" >$gs</td> <td align=\"center\" >$gu</td> <td align=\"center\" >$nz</td></tr>";
	  }
	  
	  
	  echo "</div>";
   }
   
   // Tabellenende
   echo "</table>";
   
   echo "<br><br><table border=\"0\" width=\"300\">".
   		"<tr bgcolor=#6682e4> <td><tablehead>Legende - Persönliche Bestzeit gemessen ...</tablehead></td></tr>".
   		"<div class=\"a\"><tr bgcolor=".$color[0]."><td>innerhalb der letzen 24 Stunden</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[1]."><td>innerhalb der letzen 2 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[2]."><td>innerhalb der letzen 3 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[3]."><td>innerhalb der letzen 4 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[4]."><td>innerhalb der letzen 5 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[5]."><td>innerhalb der letzen 6 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=".$color[6]."><td>innerhalb der letzen 7 Tage</td></tr></div>".
		"<div class=\"a\"><tr bgcolor=#FFFFFF><td>vor über einer Woche</td></tr></div>".
		"</table>";
   
   
   
   
   
   
 }
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
?> 



