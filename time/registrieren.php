<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
       <title>Auswertung der �bermittelten Formulardaten zur Registrieurng einer pers&ouml;nlichen Karte</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php
/**
 *******************************************************************************
 * file    regitrieren.php
 *******************************************************************************
 * brief    Skript zum Auswerten der eingegebenen Formulardaten zur Registrierung einer pers�nlichen Karte
 * 
 * version		1.0
 * date		11.06.2008
 * author		R. Zoss
 *
 *******************************************************************************
 */
 
// Funktion zum Verbinden der gew�nschten Datenbank
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
	case "joomla":	$db_joomla = @ mysql_connect ( $db_server, $db_userjoomla, $db_passwortjoomla )
							or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
					$db_check = mysql_select_db ( $db_namejoomla );
					break;
	case "time":	$db_time = @ mysql_connect ( $db_server, $db_usertime, $db_passworttime )
							or die ( 'Konnte keine Verbindung zur Datenbank herstellen' );
					$db_check = mysql_select_db ( $db_nametime );
					break;
	}
	return $db_check;
} 

        

database_connect("joomla");

// Pr�fen ob neue Eintr�ge vorhanden sind
$sql = "SELECT id, submitted FROM jos_facileforms_records WHERE form =24 AND exported =0 AND viewed=0";
$res_newentries = mysql_query($sql);
$num_newentries = mysql_affected_rows();

// Neue Eintr�ge abarbeiten
for($i=0;$i<$num_newentries;++$i){
	$record_num = mysql_result($res_newentries,$i,"id");
	$submitted = mysql_result($res_newentries,$i,"submitted");
	// Auslesen der eingetragenen Daten.
	// Dies kann mit fixen Offset gemacht werden, da immer alle Felder eingetragen werden.
	$sql = "SELECT name, value FROM jos_facileforms_subrecords WHERE record=$record_num ORDER BY id";
	$res = mysql_query($sql);
	$num = mysql_affected_rows();
	$nan = mysql_result($res,0,"value");
	$von = mysql_result($res,1,"value");
	$adr = mysql_result($res,2,"value");
	$plz = mysql_result($res,3,"value");
	$ort = mysql_result($res,4,"value");
	$nat = mysql_result($res,5,"value");
	$jah = mysql_result($res,6,"value");
	$ges = mysql_result($res,7,"value");
	$clu = mysql_result($res,8,"value");
	$ema = mysql_result($res,9,"value");
	$tel = mysql_result($res,10,"value");
	$mob = mysql_result($res,11,"value");
	$snr = mysql_result($res,12,"value");
	// Seriennummer in Grossbuchstaben wandeln (Vereinheitlichung)
	$snr = strtoupper($snr);
		
	echo "$nan $von $adr $plz $ort $nat $ema $jah $ges $clu $tel $mob $snr <br>";
	
	// Eintragen der Daten in die Time-Datenbank
	mysql_close();
	database_connect("time");
	// Pr�fen ob der Teilnehmer schon vorhanden ist
	if(strstr($adr," ")){
		// Adresse mit Hausnummer
		$adresse = substr($adr, 0, strpos($adr," ")).'%'; // Strasse extrahieren und '%' anh�ngen
	}else{
		// Adresse ohne Hausnummer
		$adresse = $adr.'%'; // Nur '%' anh�ngen
	}
	echo $adresse;
	
	$sql = "SELECT TeilnehmerKey, SNR_RFID FROM teilnehmer 
		    WHERE Name='$nan' AND Vorname='$von' AND Adresse 
		    LIKE '$adresse' AND PLZ='$plz' AND Ort='$ort'";
	$res = mysql_query($sql);
	$num = mysql_affected_rows();
	if($num == 1 && is_null(mysql_result($res,0,"SNR_RFID"))){
		// �bereinstimmung gefunden und noch keine Karte registriert
		echo "�bereinstimmung";
		$TeilnehmerKey = mysql_result($res,0,"TeilnehmerKey");
		$sql = "UPDATE teilnehmer SET SNR_RFID = '$snr' WHERE TeilnehmerKey =$TeilnehmerKey";
		mysql_query("LOCK TABLES teilnehmer WRITE");
		mysql_query($sql);
		mysql_query("UNLOCK TABLES");
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
		mysql_close();
		database_connect("joomla");
		$sql="UPDATE jos_facileforms_records SET viewed = '1', exported = '1'
			  WHERE id =$record_num";
		mysql_query("LOCK TABLES jos_facileforms_records WRITE");
		mysql_query($sql);
		mysql_query("UNLOCK TABLES");
		
	}else{
		// Keine sichere �bereinstimmung --> manuelle Eintragung
		echo "keine �bereinstimmung";
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
					Somit ist aus Sicherheitsgr�nden keine automatische Eintragung m&ouml;glich. 
					Der Administrator wird sich bei Dir per Email melden. </li>
					<li>Du hast beim Ausf�llen des Formulars nicht dieselben Angaben gemacht, 
					wie im System hinterlegt sind. (Tippfehler, Adress�nderung, usw.) Auch in diesem Fall 
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
				<br>------Informationen f�r den Admin------<br>
				Record-Nr: $record_num<br>
				Timestamp: $submitted<br>		
				Daten: $nan $von, $plz $ort<br>
				</p>
				
			</body>
		</html>
		";
		send_mail('no',$message, $vor, $nan, $ema, $REMOTE_ADDR);
		// Nur Viewed in Joomla-DB auf 1 schalten, um den Datensatz als nicht ausgewertet, aber betrachtet zu markieren
		mysql_close();
		database_connect("joomla");
		$sql="UPDATE jos_facileforms_records SET viewed = '1' WHERE id =$record_num";
		mysql_query("LOCK TABLES jos_facileforms_records WRITE");
		mysql_query($sql);
		mysql_query("UNLOCK TABLES");
	}
} // end for(...)	
	
function send_mail($typ, $message, $von, $nan, $ema, $REMOTE_ADDR) {
	// Senden eines Emails zur Best�tigung an den frisch registrierten 
	// CC an registration@beo-timing.ch zur Info
	
	/* Betreff */	
	if($typ=='success'){
	    $subject = 'Erfolgreiche Registration einer pers�nlichen Karte bei BEO-Timing';
	}else{
		$subject = 'Fehlgeschlagen: Registration einer pers�nlichen Karte bei BEO-Timing';
	}
	
	/* Empf�nger */
	$empfaenger = array($von.' '.$nan.'<'.$ema.'>');
	
	/* Empf�nger CC */
	$empfaengerCC = array('');

	/* Empf�nger BCC */
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