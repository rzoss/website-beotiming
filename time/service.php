<?php

// Dieses Skript muss stündlich aufgerufen werden!

// Spiez Aeschi
$start1 = mktime(0, 0, 0, 4, 26, 2009);
$stopp1 = mktime(0, 0, 0, 5, 31, 2009);
// Steff - Buchen
$start2 = mktime(0, 0, 0, 6,  6, 2009);
$stopp2 = mktime(0, 0, 0, 12, 7, 2009);
// Oberhofen - Schwanden
$start3 = mktime(0, 0, 0, 8, 15, 2009);
$stopp3 = mktime(0, 0, 0, 9, 20, 2009);

// Aktuelle Zeit bestimmen.
$actual   = mktime(date("s"), date("i"), date("H"), date("m")  , date("d"), date("Y"));
$midnight = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

$secondsofday = $actual-$midnight;

echo "$actual - $midnight = $secondsofday";

echo "<br>Strecke:<br>";

// Datum prüfen ob Strecke aktiv.
if(($actual > $start1 && $actual < $stopp1) || 
   ($actual > $start2 && $actual < $stopp2) || 
   ($actual > $start3 && $actual < $stopp3)){

	// Wenn 0100: Eintrag in Log "Station Ziel OK".
	if(date("H")=="01"){
		if(file_exists("ok.dat")){
			unlink("ok.dat");
			file_put_contents("service.log", "test\n", FILE_APPEND);
		}else{
			$error="Ziel nicht in Ordnung";
			file_put_contents("service.log", "notest\n", FILE_APPEND);
		}


	}
	// Wenn 0600: Eintrag in Log "Station Start OK".
	else if(date("H")=="06"){
		if(file_exists("ok.dat")){
			unlink("ok.dat");
			file_put_contents("service.log", "test\n", FILE_APPEND);
		}else{
			$error="Ziel nicht in Ordnung";
			file_put_contents("service.log", "notest\n", FILE_APPEND);
		}


	}
	else {
		file_put_contents("service.log", "nothing\n", FILE_APPEND);
	}


	// Falls Fehler, email an rico.zoss@gmail.com

}


?>
