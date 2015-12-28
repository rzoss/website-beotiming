<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Teilnehmersuche</title>
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
 * version		
 * date		    
 * author		R. Zoss
 * 
 * changelog:	
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
	if($_POST['Nachname']!=NULL || $_POST['Vorname']!=NULL){
   		$namePost = $_POST['Nachname'];
		$vornamePost = $_POST['Vorname'];
		
		$geschlechtPost = $_POST['geschlecht'];
   	 	// Personensuche für SQL Query vorbereiten (LIKE)
		$namePost="$namePost%";
		$vornamePost="$vornamePost%";
		
		$StreckenKey=$rennenPost;
		
		// alle Kategorien vorbereiten
		$kategoriePost="%";
		
		
    }else{
 		 
		 
		 // Aktuelles Jahr speichern
		 $jahrPost=$datum[year];
         // Beide Geschlechter
         $geschlechtPost="%";
		 // Personensuche
		 $namePost="%";
		 $vornamePost="%";
		 $kategoriePost="%";
		

		 	
    }
		
	//echo "$rennenPost   $namePost  $vornamePost $geschlechtPost    $jahrPost     $StreckenKey";
	
	echo "<div class=\"a\"><p>Es muss der Name und/oder Vorname eingegeben werden. Die Suche wird mit 'Go' gestartet.<br>";
	echo "Es ist auch m&ouml;glich nur den Anfang eines Namens einzugeben. Die Gross-Kleinschreibung wird nicht beachtet.<br>";
	echo "Beispiel: Die Eingabe 'mar' beim Vornamen wird zu Martin, Markus, Marcel, Martina, usw.</p></div>";
	
?>



<form action="personen.php" method="post">

<table border="0" width="660">
<tr bgcolor=#6682e4 text=#FFFFFF> <td><tablehead>Nachname</tablehead></td> <td><tablehead>Vorname</tablehead></td><td><tablehead>Geschlecht</tablehead></td></tr>
		 <?php
		 
		 echo "<td>";
		 // Inputfeld mit dem letzten Inhalt erstellen
		 $string=substr($namePost,0,strlen($namePost)-1); // '%' abschneiden
  		 echo "<input name=\"Nachname\" type=\"text\" size=\"15\" maxlength=\"30\" value=\"$string\">";
  		 echo "</td><td>";
		 
		 // Inputfeld mit dem letzten Inhalt erstellen
		 $string=substr($vornamePost,0,strlen($vornamePost)-1); // '%' abschneiden
  		 echo "<input name=\"Vorname\" type=\"text\" size=\"15\" maxlength=\"30\" value=\"$string\">";
  		 echo "</td><td>";
  		 
  		 // RadioButtons für Geschlechterwahl erstellen und den zuletzt gewählte auswählen
  		 if($geschlechtPost=="Maennlich"){
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"Maennlich\" checked=\"checked\"/>Mann";
  		 }else{
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"Maennlich\"/>Mann";
  		 }
  		 if($geschlechtPost=="Weiblich"){
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"Weiblich\" checked=\"checked\"/>Frau";
  		 }else{
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"Weiblich\"/>Frau";
  		 }
  		 if($geschlechtPost=="%"){
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"%\" checked=\"checked\"/>Beide";
  		 }else{
  		 	echo "<input type=\"radio\" name=\"geschlecht\" value=\"%\"/>Beide";
  		 }  		 
        
          
     ?>
</td>
</tr>
</table>
<input type="submit" value=" Go ">
<hr color = #6682e4>
</form>


<?php
   $sql = "SELECT teilnehmer.TeilnehmerKey, teilnehmer.name, teilnehmer.vorname, teilnehmer.ort, 
    	 teilnehmer.jahrgang,teilnehmer.club
    	 FROM strecken, zeiten, teilnehmer 
    	 WHERE zeiten.StreckenKey = strecken.StreckenKey 
   	 	 AND zeiten.TeilnehmerKey = teilnehmer.TeilnehmerKey 
   		 AND teilnehmer.Geschlecht LIKE '$geschlechtPost'  
    	 AND (teilnehmer.Name LIKE '$namePost' AND teilnehmer.Vorname LIKE '$vornamePost' )
   		 AND zeiten.kategorie LIKE '$kategoriePost' 
		 AND NOT teilnehmer.Name='(noch' 
   	 	 GROUP BY teilnehmer.TeilnehmerKey
    	 ORDER BY teilnehmer.name ASC, teilnehmer.vorname ASC, teilnehmer.jahrgang DESC"; 
   //echo $sql;
   $res = mysql_query($sql);
   $num = mysql_num_rows($res);
	//echo "$num";
	if($_POST['Nachname']==NULL && $_POST['Vorname']==NULL)
   {
		echo "<div class=\"a\"><p>Daten eingeben, um die Suche zu starten.</p></div>";
   }
   else if($num > 50)
   {
		echo "<div class=\"a\"><p>Mehr als 50 Treffer. Bitte die Suche weiter einschränken.</p></div>";
   }
   else if($num == 0)
   {
		echo "<div class=\"a\"><p>Kein Teilnehmer gefunden.</p></div>";
   }
   else
   {
		echo "<div class=\"a\"><p>$num Teilnehmer gefunden.</p></div><br>";
	   // Überschrift
		echo "<table border=\"0\" width=\"660\">";
	   echo "<tr bgcolor=#6682e4> <td><tablehead>Name</tablehead></td>";
	   echo "<td><tablehead>Vorname</tablehead></td> <td><tablehead>Ort</tablehead></td>";
	   echo "<td align=\"center\"><tablehead>Jahrgang</tablehead></td> <td><tablehead>Team / Club</tablehead></td>";
	   echo "<td align=\"center\"><tablehead>Tln.</tablehead></td></tr>";

	   for ($i=0; $i<$num; $i++)
	   {
		  $tk = mysql_result($res, $i, "TeilnehmerKey");
		  $nn = mysql_result($res, $i, "name");
		  $vn = mysql_result($res, $i, "vorname");
		  $pn = mysql_result($res, $i, "ort");
		  $ge = mysql_result($res, $i, "jahrgang");
		  $gt = mysql_result($res, $i, "club");
		  // Abfragen der Anzahl Fahrten
		  $sql = "SELECT count(*) AS anzahl FROM zeiten WHERE TeilnehmerKey= $tk";
		  $res_num = mysql_query($sql);
		  $nz = mysql_result($res_num, 0, "anzahl");		
		   // Abfragen der Anzahl Fahrten insgesamt
		  //$sql = "SELECT count(*) AS anzahl FROM zeiten WHERE TeilnehmerKey= $tk";
		  //$res_num = mysql_query($sql);
		  //$gz = mysql_result($res_num, 0, "anzahl");	
		  
		  // Entscheid für die Farbe der Markierung
		  
		  echo "<div class=\"a\">";
		  echo "<tr>";
		  // Tabellenzeile mit -zellen
		  echo "<td><a href=\"details.php?key=$tk\">$nn</a></td> <td><a href=\"details.php?key=$tk\">$vn</a></td>";
		  echo "<td>$pn</td> <td align=\"center\">$ge</td> <td>$gt</td><td align=\"center\">$nz</td></tr>";
		  echo "</div>";
	   }
	   
	   // Tabellenende
	   echo "</table>";
    }

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
?> 


