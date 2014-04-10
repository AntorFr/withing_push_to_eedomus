<?php

	require_once ("myconf.php");
    require_once ("WBSAPI.php");
   
	function myLog($str) {
		try {
			$fp = fopen('logs.txt', 'a');
			fwrite($fp, date('r')." $str\n");
			fclose($fp);
		}
		catch (Exception $e) {
		}
	}
    
    function updateMeas($user) {
        WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_SCALE);
        foreach ($measuregrps as $measuregrp) {
            foreach ( $measuregrp['measures'] as $measure ){
                if ($measure['type'] == 1) {
                    $val = round ( floatval ( $measure['value'] ) * floatval ( "1e".$measure['unit'] ),2);
                }
            }
            $url  = "https://api.eedomus.com/set?action=periph.value";
            $url .= "&api_user=".MYAPIUSER."&api_secret=".MYAPISECRET;
            $url .= "&periph_id=".$user['deviceid']."&value=".$val."&value_date=".urlencode(date('Y-m-d H:i:s',$measuregrp['date']));
        
            $result = file_get_contents($url);
            if (strpos($result, '"success": 1') == false)
            {
                myLog ( "Une erreur est survenue: [".$result."] pour l'url : ".$url);
            }
        }

    }

	if ( ( ! isset ( $_REQUEST['userid'] ) ) || ( $_REQUEST['userid'] == '' ) ) {
		myLog ( "[userid] is not set, or is empty"); exit(-1);
	}
	else {
		$userid = $_REQUEST['userid'];
		if ( $userid <= 0 ) {
			myLog ( "[userid] is invalid (".$userid.")"); exit(-1);
		}
	}
    

    foreach ($users as $user) {
        if ($user['id'] == $userid) {
            updateMeas($user);
        }
    }
    

?>
