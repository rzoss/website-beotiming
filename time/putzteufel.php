<?php 

echo "<b>Fehlerbehebungsskript wird gestartet</b>.<br><br>";

$date = date("c"); // Aktuelle Zeit in String speichern


// ftp_sema l�schen falls �lter als 30 Minuten
if(file_exists("ftp_sema")){
	$change = 0;
	$change = time() - filectime("ftp_sema"); 
	echo "<br>Datei besteht w�hrend $change Sekunden.<br>";
	$error = "<$date> Datei besteht w�hrend $change Sekunden.\n";
}else{
	exit("<br>Es gibt nichts aufzur�umen!<br>");
}

if($change > 1800){
	unlink(ftp_sema);
	echo "Datei \"ftp_sema\" wurde gel�scht.<br><br>";
	$error .= "<$date> Datei \"ftp_sema\" wurde gel�scht.\n";

	// auf vorhandene Zeiten testen
	echo "Pr�fen auf vorhandene Zeiten:<br>";
	$count = 1;
	while(file_exists("time".$count.".txt"))
	{
		echo "time".$count.".txt<br>";
		$count++;
	}
	$count--;

	// Anzahl files eintragen
	file_put_contents("time.dat",$count);
	echo "<br>\"time.dat\"-Z�hler auf $count geschrieben.<br>";
	$error .= "<$date> \"time.dat\"-Z�hler auf $count geschrieben.\n";


	// time.php aufrufen
	echo "<br>******************* START Ausgabe \"time.php\" *******************<br><br>";
	include("time.php");
	echo "<br><br>******************* ENDE Ausgabe \"time.php\" *******************<br>";

	echo "<br>Zeiten ausgewertet und Dateien gel�scht.";
	$error .= "<$date> Zeiten ausgewertet und Dateien gel�scht.\n";

}else{
	exit("<br>Die Datei \"ftp_sema\" existiert, ist jedoch noch nicht �lter als 30 Minuten.<br>");
}

// schreibe error in log und sende email.
file_put_contents("time.log",$error, FILE_APPEND);	
send_mail($error, $REMOTE_ADDR);
echo "<br>Email und Log wurden geschrieben.";





function send_mail($message, $REMOTE_ADDR) {
	// Senden eines Emails zur Best�tigung an den frisch registrierten 
	// CC an registration@beo-timing.ch zur Info
	
	/* Betreff */	
	$subject = 'Fehler auf BEO-Timing Server gefunden und beseitigt';
	
	/* Empf�nger */
	$empfaenger = array('Rico Zoss<rico.zoss@gmail.com>');
	
	/* Empf�nger CC */
	$empfaengerCC = array('Sekretariat RRC-Thun<sekretariat@rrc-thun.ch>');

	/* Empf�nger BCC */
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
