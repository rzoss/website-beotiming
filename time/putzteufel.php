<?php 

echo "<b>Fehlerbehebungsskript wird gestartet</b>.<br><br>";

$date = date("c"); // Aktuelle Zeit in String speichern


// ftp_sema löschen falls älter als 30 Minuten
if(file_exists("ftp_sema")){
	$change = 0;
	$change = time() - filectime("ftp_sema"); 
	echo "<br>Datei besteht während $change Sekunden.<br>";
	$error = "<$date> Datei besteht während $change Sekunden.\n";
}else{
	exit("<br>Es gibt nichts aufzuräumen!<br>");
}

if($change > 1800){
	unlink(ftp_sema);
	echo "Datei \"ftp_sema\" wurde gelöscht.<br><br>";
	$error .= "<$date> Datei \"ftp_sema\" wurde gelöscht.\n";

	// auf vorhandene Zeiten testen
	echo "Prüfen auf vorhandene Zeiten:<br>";
	$count = 1;
	while(file_exists("time".$count.".txt"))
	{
		echo "time".$count.".txt<br>";
		$count++;
	}
	$count--;

	// Anzahl files eintragen
	file_put_contents("time.dat",$count);
	echo "<br>\"time.dat\"-Zähler auf $count geschrieben.<br>";
	$error .= "<$date> \"time.dat\"-Zähler auf $count geschrieben.\n";


	// time.php aufrufen
	echo "<br>******************* START Ausgabe \"time.php\" *******************<br><br>";
	include("time.php");
	echo "<br><br>******************* ENDE Ausgabe \"time.php\" *******************<br>";

	echo "<br>Zeiten ausgewertet und Dateien gelöscht.";
	$error .= "<$date> Zeiten ausgewertet und Dateien gelöscht.\n";

}else{
	exit("<br>Die Datei \"ftp_sema\" existiert, ist jedoch noch nicht älter als 30 Minuten.<br>");
}

// schreibe error in log und sende email.
file_put_contents("time.log",$error, FILE_APPEND);	
send_mail($error, $REMOTE_ADDR);
echo "<br>Email und Log wurden geschrieben.";





function send_mail($message, $REMOTE_ADDR) {
	// Senden eines Emails zur Bestätigung an den frisch registrierten 
	// CC an registration@beo-timing.ch zur Info
	
	/* Betreff */	
	$subject = 'Fehler auf BEO-Timing Server gefunden und beseitigt';
	
	/* Empfänger */
	$empfaenger = array('Rico Zoss<rico.zoss@gmail.com>');
	
	/* Empfänger CC */
	$empfaengerCC = array('Sekretariat RRC-Thun<sekretariat@rrc-thun.ch>');

	/* Empfänger BCC */
	$empfaengerBCC = array('');

	/* Absender */
	$absender = 'Error - BEO-Timing<error@beo-timing.ch>';

	/* Rueckantwort */
	$reply = 'Administrator - BEO-Timing<admin@beo-timing.ch>';
	
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
