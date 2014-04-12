<?
if (file_exists('myconf.php.perso')) {
	include ('myconf.php.perso');
}
else {
    // Withing
	define ('MYMAIL','WITHING_EMAIL');
	define ('MYPASS','WITHING_PASSWORD');
	define ('MYURL','http://VOTRESERVEUR/Withing/callback.php');
	define ('MYWBSAPIURL','wbsapi.withings.net/');
	define ('MYAPIURL','scalews.withings.net/cgi-bin/');
    
    //Eedomus
    define ('MYAPIUSER','VOTRE_APIUSER');
    define ('MYAPISECRET','VOTRE_APISECRET' );
    define ('MYDEVICEID','VOTRE_DEVICE_ID' );
    
    
    // Lien Eedomus - Withing :
    $users = array(
        // Pour chaque utilisateur Withing configuré
        array ('id' => '<EEDOMUS_DEVICE_ID>', 'publickey' => '<WITHING_CLE_UTILISATEUR>', 'deviceid' => '<EEDOMUS_DEVICE_ID>', 'pseudo' => '<PSEUDO_WITHING>'),
        array ('id' => '<EEDOMUS_DEVICE_ID>', 'publickey' => '<WITHING_CLE_UTILISATEUR>', 'deviceid' => '<EEDOMUS_DEVICE_ID>', 'pseudo' => '<PSEUDO_WITHING>')
    );

}
?>
