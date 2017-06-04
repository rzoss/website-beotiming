<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
       <title>Auswertung der übermittelten Formulardaten zur Registrieurng einer pers&ouml;nlichen Karte</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php
/**
 *******************************************************************************
 * file    regitrieren.php
 *******************************************************************************
 * brief    Skript zum Auswerten der eingegebenen Formulardaten zur Registrierung einer persönlichen Karte
 * 
 * version		2.0
 * date		04.06.2017
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
 
// Funktion zum Verbinden der gewünschten Datenbank
function database_connect($dbname) {
	/* Datenbankserver - In der Regel die IP */
	$db_server = 'ricozo6.mysql.db.internal';

	/* Datenbankname */
	$db_namejoomla = 'ricozo6_beotimingjoomlaV1';
	$db_nametime = 'ricozo6_beotimingtime';
	/* Datenbankuser */
	$db_userjoomla = 'ricozo6_beotim';
	$db_usertime = 'ricozo6_beotim';
	/* Datenbankpasswort */
	$db_passwortjoomla = 'KGloQyRd';
    $db_passworttime = 'KGloQyRd';
	
	
	
	/* Erstellt Connect zu Datenbank her */
	switch($dbname){
	case "joomla":	$db_joomla = @ mysqli_connect ( $db_server, $db_userjoomla, $db_passwortjoomla, $db_namejoomla )
							or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
					break;
	case "time":	$db_time = @ mysqli_connect ( $db_server, $db_usertime, $db_passworttime, $db_nametime )
							or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
					break;
	}
	return $db_check;
} 

        

database_connect("joomla");

// Prüfen ob neue Einträge vorhanden sind
$sql = "SELECT id, submitted FROM jos_facileforms_records WHERE form =24 AND exported =0 AND viewed=0";
$res_newentries = mysqli_query($db, $sql);
$num_newentries = mysqli_affected_rows();

// Neue Einträge abarbeiten
for($i=0;$i<$num_newentries;++$i){
	$record_num = mysqli_result($res_newentries,$i,"id");
	$submitted = mysqli_result($res_newentries,$i,"submitted");
	// Auslesen der eingetragenen Daten.
	// Dies kann mit fixen Offset gemacht werden, da immer alle Felder eingetragen werden.
	$sql = "SELECT name, value FROM jos_facileforms_subrecords WHERE record=$record_num ORDER BY id";
	$res = mysqli_query($db, $sql);
	$num = mysqli_affected_rows();
	$nan = mysqli_result($res,0,"value");
	$von = mysqli_result($res,1,"value");
	$adr = mysqli_result($res,2,"value");
	$plz = mysqli_result($res,3,"value");
	$ort = mysqli_result($res,4,"value");
	$nat = mysqli_result($res,5,"value");
	$jah = mysqli_result($res,6,"value");
	$ges = mysqli_result($res,7,"value");
	$clu = mysqli_result($res,8,"value");
	$ema = mysqli_result($res,9,"value");
	$tel = mysqli_result($res,10,"value");
	$mob = mysqli_result($res,11,"value");
	$snr = mysqli_result($res,12,"value");
	// Seriennummer in Grossbuchstaben wandeln (Vereinheitlichung)
	$snr = strtoupper($snr);
		
	echo "$nan $von $adr $plz $ort $nat $ema $jah $ges $clu $tel $mob $snr <br>";
	
	// Eintragen der Daten in die Time-Datenbank
	mysqli_close();
	database_connect("time");
	// Prüfen ob der Teilnehmer schon vorhanden ist
	if(strstr($adr," ")){
		// Adresse mit Hausnummer
		$adresse = substr($adr, 0, strpos($adr," ")).'%'; // Strasse extrahieren und '%' anhängen
	}else{
		// Adresse ohne Hausnummer
		$adresse = $adr.'%'; // Nur '%' anhängen
	}
	echo $adresse;
	
	$sql = "SELECT TeilnehmerKey, SNR_RFID FROM teilnehmer 
		    WHERE Name='$nan' AND Vorname='$von' AND Adresse 
		    LIKE '$adresse' AND PLZ='$plz' AND Ort='$ort'";
	$res = mysqli_query($db, $sql);
	$num = mysqli_affected_rows();
	if($num == 1 && is_null(mysqli_result($res,0,"SNR_RFID"))){
		// Übereinstimmung gefunden und noch keine Karte registriert
		echo "Übereinstimmung";
		$TeilnehmerKey = mysqli_result($res,0,"TeilnehmerKey");
		$sql = "UPDATE teilnehmer SET SNR_RFID = '$snr' WHERE TeilnehmerKey =$TeilnehmerKey";
		mysqli_query($db, "LOCK TABLES teilnehmer WRITE");
		mysqli_query($db, $sql);
		mysqli_query($db, "UNLOCK TABLES");
		/* Nachricht */
		$message = "<html>
			<head>
				<title>BEO-Timing - Registration</title>
				<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.rrc-thun.ch/time/template_css.css\" />
			</head>
			<body>
				<p>Lieber $von<br>
				<br>
				Die Karte mit der Seriennummer \"$snr\" wurde auf den Namen $nan $von registriert.
				Ab sofort wird deine Fahrzeit nach der Teilnahme an einem Rennen von BEO-Timing, 
				umgehend deinem Namen zugeordnet und erscheint in der Rangliste unter deinem Namen.<br>
				<br>
				Wir w&uuml;nschen dir viel Spass bei der Teilnahme an den verschiedenen Wettbewerben.<br>
				<br>
				Mit sportlichen Gr&uuml;ssen<br>
				<br><br>
				BEO-Timing Registration<br>		
				<br>
				<a href=\"http://www.beo-timing.ch\">http://www.beo-timing.ch</a><br>
				Getragen durch:<br>
				<a href=\"http://www.rrc-thun.ch\">Radrennclub Thun</a><br>
				<a href=\"http://www.rc-steffisburg.ch\">Racingclub Steffisburg</a><br>
				</p>
			</body>
		</html>
		";
		send_mail('success', $message, $vor, $nan, $ema, $REMOTE_ADDR);
		
		// Exported und Viewed in Joomla-DB auf 1 schalten, um den Datensatz als ausgewertet zu markieren
		mysqli_close();
		database_connect("joomla");
		$sql="UPDATE jos_facileforms_records SET viewed = '1', exported = '1'
			  WHERE id =$record_num";
		mysqli_query($db, "LOCK TABLES jos_facileforms_records WRITE");
		mysqli_query($db, $sql);
		mysqli_query($db, "UNLOCK TABLES");
		
	}else{
		// Keine sichere Übereinstimmung --> manuelle Eintragung
		echo "keine Übereinstimmung";
		/* Nachricht */
		$message = "<html>
			<head>
				<title>BEO-Timing - Registration</title>
				<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.rrc-thun.ch/time/template_css.css\" />
			</head>
			<body>
				<p>Lieber $von<br><br>
				Die Karte mit der Seriennummer \"$snr\" konnte nicht automatisch auf den Namen $nan $von registriert werden. <br>
					Dies kann die folgenden Gr&uuml;nde haben: <br><ol>
					<li>Deine Daten sind im System noch nicht Registriert, d.h Du hast noch an keinem Rennen teilgenommen. 
					Somit ist aus Sicherheitsgründen keine automatische Eintragung m&ouml;glich. 
					Der Administrator wird sich bei Dir per Email melden. </li>
					<li>Du hast beim Ausfüllen des Formulars nicht dieselben Angaben gemacht, 
					wie im System hinterlegt sind. (Tippfehler, Adressänderung, usw.) Auch in diesem Fall 
					wird der Administrator mit dir per Email in Kontakt treten. </li>
					<li>Auf deinen Namen ist bereits eine Karte registriert. In diesem Fall bitten wir dich 
					mit dem Administrator in Kontakt zu treten, um das Problem aufzul&ouml;sen.</li>
				</ol>
				<br>
				Den Administrator erreichst Du unter der Emailadresse 
				<a href=\"mailto:admin@beo-timing.ch\">admin@beo-timing.ch</a>
				oder durch Anworten auf dieses Email (Bitte den Betreff ab&auml;ndern).
				<br><br>
				Mit sportlichen Gr&uuml;ssen<br>
				<br>
				<br>
				BEO-Timing Registration<br>		
				<br>
				<a href=\"http://www.beo-timing.ch\">http://www.beo-timing.ch</a><br>
				Getragen durch:<br>
				<a href=\"http://www.rrc-thun.ch\">Radrennclub Thun</a><br>
				<a href=\"http://www.rc-steffisburg.ch\">Racingclub Steffisburg</a><br>
				<br>
				<br>------Informationen für den Admin------<br>
				Record-Nr: $record_num<br>
				Timestamp: $submitted<br>		
				Daten: $nan $von, $plz $ort<br>
				</p>
				
			</body>
		</html>
		";
		send_mail('no',$message, $vor, $nan, $ema, $REMOTE_ADDR);
		// Nur Viewed in Joomla-DB auf 1 schalten, um den Datensatz als nicht ausgewertet, aber betrachtet zu markieren
		mysqli_close();
		database_connect("joomla");
		$sql="UPDATE jos_facileforms_records SET viewed = '1' WHERE id =$record_num";
		mysqli_query($db, "LOCK TABLES jos_facileforms_records WRITE");
		mysqli_query($db, $sql);
		mysqli_query($db, "UNLOCK TABLES");
	}
} // end for(...)	
	
function send_mail($typ, $message, $von, $nan, $ema, $REMOTE_ADDR) {
	// Senden eines Emails zur Bestätigung an den frisch registrierten 
	// CC an registration@beo-timing.ch zur Info
	
	/* Betreff */	
	if($typ=='success'){
	    $subject = 'Erfolgreiche Registration einer persönlichen Karte bei BEO-Timing';
	}else{
		$subject = 'Fehlgeschlagen: Registration einer persönlichen Karte bei BEO-Timing';
	}
	
	/* Empfänger */
	$empfaenger = array($von.' '.$nan.'<'.$ema.'>');
	
	/* Empfänger CC */
	$empfaengerCC = array('');

	/* Empfänger BCC */
	if($typ=='success'){
		$empfaengerBCC = array('Registration - BEO-Timing<registration@beo-timing.ch>');
	}else{
		$empfaengerBCC = array('Administrator - BEO-Timing<admin@beo-timing.ch>');
	}

	/* Absender */
	$absender = 'Registration - BEO-Timing<registration@beo-timing.ch>';

	/* Rueckantwort */
	if($typ=='success'){
		$reply = 'Registration - BEO-Timing<registration@beo-timing.ch>';
	}else{
		$reply = 'Administrator - BEO-Timing<admin@beo-timing.ch>';
	}
	
	/* Baut Header der Mail zusammen */
	$headers .= 'From:' . $absender . "\n";
	$headers .= 'Reply-To:' . $reply . "\n"; 
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\n"; 
	$headers .= 'X-Sender-IP: ' . $REMOTE_ADDR . "\n"; 
	$headers .= "Content-type: text/html\n";
	
	// Extrahiere Emailadressen
	$empfaengerString = implode(',', $empfaenger);
	$empfaengerCCString = implode(',', $empfaengerCC);
	$empfaengerBCCString = implode(',', $empfaengerBCC);
	
	$headers .= 'Cc: ' . $empfaengerCCString . "\n";
	$headers .= 'Bcc: ' . $empfaengerBCCString . "\n";

	/* Verschicken der Mail */
	mail($empfaengerString, $subject, $message, $headers);
}

?>

</body>
</html>
