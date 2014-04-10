#!/usr/bin/php
<?
require_once ("myconf.php");
require_once ("WBSAPI.php");

if (MYPASS=='') {
	echo "Edit 'myconf.php' and set MYMAIL and MYPASS to your Withings Account\n";
	exit(-1);
}


echo "Probing API : ";
if (WBSAPI_OnceProbe ()) {
	echo "OK\n"; } else { echo "KO\n"; exit(-1);
}

echo "Getting UserList : ";
if (WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $userslist)) {
	echo "OK\n"; } else { echo "KO\n"; exit(-1);
}

$listtoreload = false;
echo "Ensuring all users are public : ";
foreach ($userslist as $user) {
	$name = $user['firstname']." ".$user['lastname'];
	if ($user['ispublic']!='1') {
		$listtoreload = true;
		WBSAPI_UserUpdate ( $user['id'], $user['publickey'], 1 );
	}
}
echo "OK\n";

// When a user has been set or reset to "public", its public key changes... We need to reload the list in that case .
if ($listtoreload) {
	echo "Getting UserList : ";
	if (WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $userslist)) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
}


foreach ($userslist as $user) {
	$name = $user['firstname']." ".$user['lastname'];
	echo "[".$name."] Loading scale measures : ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_SCALE )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp , 'SI');
	}
	
	echo "[".$name."] Loading tensiometer measures : ";
	if (WBSAPI_MeasureGetmeas ( $user['id'], $user['publickey'] , $measuregrps , 0 , 0 , DEVICE_TYPE_TENSIOMETER )) {
		echo "OK\n"; } else { echo "KO\n"; exit(-1);
	}
	foreach ( $measuregrps as $measuregrp ) {
		echo WBSAPI_Helper_MeasureGrp ( $measuregrp , 'SI');
	}
}


?>
