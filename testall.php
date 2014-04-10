#!/usr/bin/php
<?
require_once ("myconf.php");
require_once ("WBSAPI.php");

echo "Probing API : ";
if (WBSAPI_OnceProbe ()) {
	echo "OK\n"; } else { echo "KO\n"; exit(-1);
}

echo "Getting UserList : ";
if (WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $userslist)) {
	echo "OK\n"; } else { echo "KO\n"; exit(-1);
}

// Printing UserList 
// print_r($userslist);

$listtoreload = false;
echo "Ensuring all users are public : \n";
foreach ($userslist as $user) {
	$name = $user['firstname']." ".$user['lastname'];
	if ($user['ispublic']!= ( DEVICE_TYPE_SCALE | DEVICE_TYPE_TENSIOMETER ) ) {
		$listtoreload = true;
		echo "  [".$name."] Set to public : ";
		if (WBSAPI_UserUpdate ( $user['id'], $user['publickey'], DEVICE_TYPE_SCALE | DEVICE_TYPE_TENSIOMETER )) {
			echo "OK\n"; } else { echo "KO\n"; exit(-1);
		}
	} else {
		echo "  [".$name."] Already public  : OK\n";
	}
}

// When a user has been set or reset to "public", its public key changes... We need to reload the list in that case .
if ($listtoreload) {
	echo "Getting UserList : ";
	if (WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $userslist)) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
}


foreach ($userslist as $user) {
	$name = $user['firstname']." ".$user['lastname'];
	echo "  [".$name."] Re-Loading... : ";
	if (WBSAPI_UserGetbyuserid ( $user['id'], $user['publickey'] , $userbis )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
// We check that what we have downloaded with User/Getbyuserid is identical to what was download by Account/Getuserslist
	echo "      User/Getbyuserid Account/Getuserslist : ";
	unset ($user['publickey']);
	if ($user==$userbis) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
}

foreach ($userslist as $user) {

        $name = $user['firstname']." ".$user['lastname'];
                
	echo "[".$name."] Loading Scale measures : ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_SCALE )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp );
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp , 'US');
	}

	echo "[".$name."] Loading Scale measures (weight only): ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_SCALE , 1 , -1 , 1 )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp );
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp , 'US');
	}

	echo "[".$name."] Loading Scale measures (height only): ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_USER , 1 , -1 , 4 )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp );
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp , 'US');
	}

	echo "[".$name."] Loading Tensiometer measures : ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_TENSIOMETER )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp );
	}
}

//	For each user, try a subscribe, get, revoke notifications sequence
if ( defined('MYURL') ) {
	foreach ($userslist as $user) {
		$name = $user['firstname']." ".$user['lastname'];

		echo "[".$name."] Subscribing notifications : ";
		if (WBSAPI_NotifySubscribe ( $user['id'], $user['publickey'] , MYURL )) {
			echo "OK\n"; } else { echo "KO\n"; exit(-1);
		}

		echo "[".$name."] Getting notifications : ";
		if (WBSAPI_NotifyGet ( $user['id'], $user['publickey'] , MYURL , $expires , $comment )) {
			echo "OK\texpires=".$expires." comment=".$comment."\n";
		}
		else { echo "KO\n"; exit(-1);
		}

		echo "[".$name."] Revoking notifications : ";
		if (WBSAPI_NotifyRevoke ( $user['id'], $user['publickey'] , MYURL )) {
			echo "OK\n"; } else { echo "KO\n"; exit(-1);
		}
	}
}
?>
