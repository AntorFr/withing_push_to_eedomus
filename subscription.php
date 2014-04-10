#!/usr/bin/php
<?
require_once ("myconf.php");
require_once ("WBSAPI.php");


echo "Getting UserList : ";
if (WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $userslist)) {
	echo "OK\n"; } else { echo "KO\n"; exit(-1);
}

//	For each user, try a subscribe, get, revoke notifications sequence
if ( defined('MYURL') ) {
	foreach ($userslist as $user) {
		$name = $user['firstname']." ".$user['lastname'];

		echo "[".$name."] Subscribing notifications : ";
		if (WBSAPI_NotifySubscribe ( $user['id'], $user['publickey'] , MYURL )) {
			echo "OK\n"; } else { echo "KO\n"; exit(-1);
		}
        /*
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
        */
	}
}
?>
