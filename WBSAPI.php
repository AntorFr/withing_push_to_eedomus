<?

require_once ("myconf.php");

define ('pound',0.453592);
define ('inch', 0.0254);

define ('DEVICE_TYPE_USER',0);
define ('DEVICE_TYPE_SCALE',1);
define ('DEVICE_TYPE_TENSIOMETER',4);

function CurlCall ( $service , &$result=null )
{
	try {
		$s = curl_init();
		curl_setopt($s,CURLOPT_URL,MYWBSAPIURL.$service);
        	curl_setopt($s,CURLOPT_POST,false);
        	curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
        	$output = curl_exec($s);
        	curl_close($s);
		$result = json_decode ( $output , TRUE );
		if (!is_array($result)) return (false);
		if (!key_exists('status',$result)) return (false);
		if ($result['status']!=0) return (false);
		return ( true );
	} catch ( Exception $e ) {
		return ( false );
	}
}

function WBSAPI_OnceProbe ()
{
	return ( CurlCall ( "once?action=probe" , $result) );
}

function WBSAPI_AccountGetuserslist ( $email, $password , &$userslist )
{
	$userslist = Array ();
	if (CurlCall ( "once?action=get", $result)===false) return (false);
	$once = $result['body']['once'];
	$hash = md5 ( strtolower($email).":".md5($password).":".$once);
	if (CurlCall ( "account?action=getuserslist&email=".strtolower($email)."&hash=".$hash, $result)===false) return (false);
	$userslist = $result['body']['users'];
	return (true);
}

function WBSAPI_UserGetbyuserid ( $userid, $publickey , &$user )
{
	if (CurlCall ( "user?action=getbyuserid&userid=".$userid."&publickey=".$publickey,$result)===false) return ( false );;
	$user = $result['body']['users']['0'];
	return (true);
}

function WBSAPI_UserUpdate ( $userid, $publickey , $ispublic )
{
	return(CurlCall ( "user?action=update&userid=".$userid."&publickey=".$publickey."&ispublic=".$ispublic));

}

function WBSAPI_MeasureGetmeas ( $userid, $publickey , &$measuregrps, $startdate=0, $enddate=0, $devtype=0, $category=1, $limit=-1, $meastype=-1 )
{
	$measuregrps = array ( );
	$offset = 0;

	do {
		$string="measure?action=getmeas&userid=".$userid."&publickey=".$publickey."&category=".$category."&limit=".$limit."&offset=".$offset;
		if ($startdate!=0) $string.="&startdate=".$startdate;
		if ($enddate  !=0) $string.="&enddate=".$enddate;
		if ($devtype !=0) $string.="&devtype=".$devtype;
		if ($meastype !=0) $string.="&meastype=".$meastype;
		if (CurlCall ( $string,$result)===false) return ( false );
		$measuregrps +=  $result['body']['measuregrps'];
		if ( array_key_exists('more', $result['body'] ) ) {
			$offset = $result['body']['more'];
		}
		else $offset = 0;
	} while ( $offset > 0 );

	return (true);
}

function WBSAPI_Helper_MeasureGrp ( $measuregrp , $system='SI')
{
	$string = date('r',$measuregrp['date'])."\n";
	foreach ( $measuregrp['measures'] as $measure ) {
		$string .="  ";
		$string .= WBSAPI_Helper_Measure ( $measure, $system );
		$string .="\n";
	}
	return($string);
}

function WBSAPI_Helper_Measure ( $measure , $system='SI')
{
	$val = floatval ( $measure['value'] ) * floatval ( "1e".$measure['unit'] );
	if ( ($measure['type'] == 1) || ($measure['type'] == 5) || ($measure['type'] == 8) ) {
		if ($system=='US') {
			$val = $val / pound;
			$val = round ( $val , 2);
			$string = $val." lb";
		} else {
			$val = round ( $val , 2);
			$string = $val." Kg";
		}
		if ($measure['type'] == 1) $string = "Weight  ".$string;
		if ($measure['type'] == 5) $string = "FatFree ".$string;
		if ($measure['type'] == 8) $string = "FAT     ".$string;
	} elseif ($measure['type'] == 4) {
		if ($system=='US') {
			$inchs = round($val/inch);
			$foot  = floor($inchs/12);
			$inch  = $inchs-12*$foot;
			$string = $foot." ft ".$inch." in";
		} else {
			$val = round ( $val , 2);
			$string = $val." m";
		}
		$string = "Size    ".$string;
	} elseif ($measure['type'] == 6) {
		$string = "FAT %   ".$val." %";
	}
        else if ($measure['type'] == 9) {
                $string = "Diastolic Pressure  ".$val." mmHg";
        }
	else if ($measure['type'] == 10) {
		$string = "Systolic Pressure   ".$val." mmHg";
	}
	else if ($measure['type'] == 11) {
		$string = "Heart Pulse         ".$val." bpm";
	} else {
		$string = "Unknown type";
	}
	return ($string);
}

function WBSAPI_NotifySubscribe ( $userid, $publickey , $callbackurl )
{
	$string="notify?action=subscribe&userid=".$userid."&publickey=".$publickey."&callbackurl=".urlencode($callbackurl);
	if (CurlCall ( $string,$result)===false) return ( false );
	return (true);
}

function WBSAPI_NotifyRevoke ( $userid, $publickey , $callbackurl )
{
	$string="notify?action=revoke&userid=".$userid."&publickey=".$publickey."&callbackurl=".$callbackurl;
	if (CurlCall ( $string,$result)===false) return ( false );
	return (true);
}

function WBSAPI_NotifyGet ( $userid, $publickey , $callbackurl , &$expires , &$comment )
{
	$string="notify?action=get&userid=".$userid."&publickey=".$publickey."&callbackurl=".$callbackurl;
	if (CurlCall ( $string,$result)===false) return ( false );
	$expires = $result['body']['expires'];
	$comment = $result['body']['comment'];
	return (true);
}

?>
