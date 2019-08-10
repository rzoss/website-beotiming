<?php
/**
 *******************************************************************************
 * file    configStart.php
 *******************************************************************************
 * brief    Generieren der Start Konfiguration
 * 
 * version		1.0.0
 * date			19.04.2019
 * author		R. Zoss
 *
 * changelog:	- Initial 
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

if (!function_exists('write_ini_file')) {
    /**
     * Write an ini configuration file
     * 
     * @param string $file
     * @param array  $array
     * @return bool
     */
    function write_ini_file($file, $array = []) {
        // check first argument is string
        if (!is_string($file)) {
            throw new \InvalidArgumentException('Function argument 1 must be a string.');
        }

        // check second argument is array
        if (!is_array($array)) {
            throw new \InvalidArgumentException('Function argument 2 must be an array.');
        }

        // process array
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }

        // open file pointer, init flock options
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;

        if (!$fp) {
            return false;
        }

        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }

        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
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


$currdate = date('Y-m-d H:i:s');
echo "$currdate";

// Aktuelle Strecke lesen
$sql = "SELECT * FROM `strecken` WHERE StartDatumConfig<='$currdate' AND EndDatumConfig>='$currdate'";
$res = mysqli_query($db, $sql);

$sk = mysqli_result($res, 0, "StreckenKey");
$rt = mysqli_result($res, 0, "RoadType");
$sn = mysqli_result($res, 0, "ShortName");

echo "<p><b>$sk</b><br>$rt<br>$sn</p><br>";


// Ohne Gruppen analysieren
$ini_array = parse_ini_file("start/config.ini",true);

$ini_array['Route']['RouteName']=$sn;
$ini_array['Route']['RouteKey1']=$sk;
$ini_array['Route']['RouteType1']=$rt;

// print ini file
print_r($ini_array);

// write ini file
write_ini_file('start/config.ini', $ini_array);

?>