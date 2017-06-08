<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
       <title>Auswertung der übermittelten Daten im Verzeichnis</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php

/**
 *******************************************************************************
 * file    time.php
 *******************************************************************************
 * brief    Skript zum Auswerten der über GPRS übermittelten Fahrzeiten der mobilen Stationen
 * 
 * version		2.0.0
 * date			04.06.2017
 * author		R. Zoss
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
   		or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
	
		// Aktuelles Datum holen
	$datum = getdate(time());
	$date = date("c");
	$curYear = date('Y');

$fileprefix = "time";
$filenumber = 1;
$fileext = ".txt";


/**
 * Tests if a string is a valid mysql date.
 *
 * @param   string   date to check
 * @return  boolean
 */
function validateMysqlDate( $date )
{
    return preg_match('/\\A(?:^((\\d{2}(([02468][048])|([13579][26]))[\\-\\/\\s]?((((0?[13578])|(1[02]))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])))))|(\\d{2}(([02468][1235679])|([13579][01345789]))[\\-\\/\\s]?((((0?[13578])|(1[02]))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\\-\\/\\s]?((0?[1-9])|(1[0-9])|(2[0-8]))))))(\\s(((0?[0-9])|(1[0-9])|(2[0-3]))\\:([0-5][0-9])((\\s)|(\\:([0-5][0-9])))?))?$)\\z/', $date);
}

// Prüfen ob die Daten durch eine "Semaphore" geschützt sind!
if(!file_exists("ftp_sema")){
	// Prüfen ob die Datei "time.dat" auf dem Server '0' enthält.
	// In diesem Fall sind keine Zeiten zu interpretieren.
	if(file_exists("$fileprefix.dat")){
		echo "\nDatei $fileprefix.dat existiert\n";
		$datei = fopen("$fileprefix.dat","r");                   // Datei öffnen
		$zeile = fgets($datei,10);                    // Zeile lesen (max. 1000 Zeichen)
		fclose($datei);                               // Datei schliessen
		echo $zeile;                                  // Daten ausgeben
		$count=$zeile;
	}else{
		echo "\nDatei $fileprefix.dat existiert nicht\n";
		$count=0;
	}
	// Ausführen wenn und dann so lange wie Dateien vorhanden sind
	for($filenumber=1;$filenumber<=$count;$filenumber++){
		echo "\nentering for-loop\n";
		$filename = "$fileprefix$filenumber$fileext";   // Zusammensetzen des Dateinamens
		echo "$filename <br>";                          // Prüfen ob die Datei vorhanden ist
		$exists = file_exists($filename);
		if($exists){
		echo "\nDatei $filename existiert\n";
		$datei = fopen($filename,"r");                // Datei öffnen
		$zeile = fgets($datei,1000);                  // Zeile lesen (max. 1000 Zeichen)
		fclose($datei);                               // Datei schliessen
		echo $zeile;                                  // Daten ausgeben


		$magic=strtok($zeile,";");					 // String parsen auf ';'
		$strecke=strtok(";");
		$starttime=strtok(";");                
		$endtime=strtok(";");
		$racetime=strtok(";");
		$rfid_snr=strtok(";");
		// MySQL Datenbank abfragen, ob die RFID-Snr fix vergeben ist
		$sql = "SELECT `TeilnehmerKey`
				FROM `teilnehmer`
				WHERE `SNR_RFID` = \"$rfid_snr\" AND `name`!='(noch'";
		$res = mysqli_query($db, $sql);
		$num = mysqli_affected_rows($db);
		echo "Resultat feste Abfrage: $num";
		
		// plausibility check:
		if(!validateMysqlDate($starttime) || !validateMysqlDate($endtime))
		{
			$magic="IGNO";
		}

		
echo "<br>Debug2 $magic<br>";
		switch($magic) {
		case "TIMW": // Zeit eintragen
				echo "\nEnter case TIMW\n";
				if($num>0){
					echo "Nummer vergeben: ";
					// Teilnehmernummer setzen falls vergeben
					$teilnehmer = mysqli_result($res,0,"TeilnehmerKey");
				}else{
					echo "Nummer frei: ";
					// "(noch nicht ausgewertet)" - User erstellen
					$sql = "INSERT INTO `$db_name`.`teilnehmer` (
							`TeilnehmerKey` ,
							`Name` ,
							`Vorname` ,
							`Adresse` ,
							`PLZ` ,
							`Ort` ,
							`Jahrgang` ,
							`Geschlecht` ,
							`Nationalitaet` ,
							`Club` ,
							`EMail` ,
							`Telefon` ,
							`Mobile` ,
							`SNR_RFID`
						)
						VALUES (
							NULL , '(noch', 'nicht', 'n/a', '0', 'ausgewertet)', '$datum[year]', '0', 'CH',
							NULL , NULL , NULL , NULL , '$rfid_snr'
						)";
					//echo $sql;
					mysqli_query($db, "LOCK TABLES teilnehmer WRITE");
					mysqli_query($db, $sql);
					mysqli_query($db, "UNLOCK TABLES");
					// TeilnehmerKey des soeben erstellten Users abfragen (Identifikation über RFID-Snr)
					// max(..) damit sicher der soeben erstellte User gelesen wird
					$sql = "SELECT TeilnehmerKey 	
						FROM `teilnehmer`
						WHERE `SNR_RFID` = '$rfid_snr' AND `name`='(noch'";
					$res = mysqli_query($db, $sql);
					$teilnehmer = mysqli_result($res,0);
					echo $teilnehmer;
				} // end if($num>0)...else 
				
				// TeilnehmerKey ausgeben
				// echo "<br>Teilnehmer: $teilnehmer<br>";
		
				$sql = "INSERT INTO `$db_name`.`zeiten`(
						`TimeKey`, 
						`Eintrag`, 
						`Startzeit`,
						`Endzeit`, 
						`StreckenKey`, 
						`TeilnehmerKey`, 
						`Kategorie`, 
						`Fahrzeit`,
						`SNR_RFID`
					)
						VALUES (
						NULL, CURRENT_TIMESTAMP, '$starttime',
						'$endtime', '$strecke', '$teilnehmer', 
						' ', '$racetime', '$rfid_snr')";
				// SQL-Befehl ausgeben
				mysqli_query($db, "LOCK TABLES zeiten WRITE");
				mysqli_query($db, $sql);  // Eintrag der neuen Zeit in die Datenbank
				mysqli_query($db, "UNLOCK TABLES");
				echo "<$date$> $zeile<br>";
				// Zeile in Log schreiben
				file_put_contents("time$curYear.log","<$date$> $zeile\n", FILE_APPEND);
				unlink($filename);              // Löschen der ausgewerteten Date
				break;
			case "TIMD":
				echo "\nEnter case TIMD\n";
				if($num==0){
					// "(noch nicht ausgewertet)" - User in DB löschen
					$sql = "SELECT `TeilnehmerKey` FROM `$db_name`.`zeiten` WHERE `Startzeit`='$starttime' AND `StreckenKey`='$strecke'";
					$res = mysqli_query($db, $sql);
					$teilnehmer = mysqli_result($res,0,"TeilnehmerKey");
					$sql = "DELETE FROM `teilnehmer` WHERE `TeilnehmerKey`=$teilnehmer";
					echo "<br>$sql<br>";
					mysqli_query($db, "LOCK TABLES teilnehmer WRITE");
					mysqli_query($db, $sql);
					mysqli_query($db, "UNLOCK TABLES");
				}// end if($num!=0)
				
				// Zeit löschen	
				$sql = "DELETE FROM `zeiten` WHERE `Startzeit`='$starttime' AND `StreckenKey`='$strecke'";
				echo "<br>$sql<br>";
				mysqli_query($db, "LOCK TABLES teilnehmer WRITE");
				mysqli_query($db, $sql); // Eintrag der neuen Zeit in die Datenbank
				mysqli_query($db, "UNLOCK TABLES");
				echo "<$date$> $zeile<br>";
				// Zeile in Log schreiben
				file_put_contents("time$curYear.log","<$date$> $zeile\n", FILE_APPEND);
				unlink($filename);              // Löschen der ausgewerteten Datei
				break;
			case "IGNO":
				echo "<$date$> Invalid date (this will be ignored): $zeile<br>";
				file_put_contents("time$curYear.log","<$date$> Invalid date (this will be ignored): $zeile\n", FILE_APPEND);
				unlink($filename);              // Löschen der ausgewerteten Datei
				break;
			} // end switch(...) case:
		} // end if($exists)
	} // end for
	
	// Erstellen der Datei "time.dat" mit Inhalt '0', als Zeichen, dass keine Daten vorhanden sind
	// (Muss beim Eintragen neuer Daten über FTP durch das Telit-Modul neu gesetzt werden)
	$handle = fopen("$fileprefix.dat", "w");
	fwrite($handle, '0');
	fclose($handle);

	mysqli_close($db);  // Logout der Datenbank
} // End semaphore
	
// Ende PHP
?>

</body>
</html>

