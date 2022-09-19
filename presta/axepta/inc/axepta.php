<?php


if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


include_spip('inc/bank');
include_spip('presta/axepta/inc/function.inc');
include_spip('presta/axepta/inc/blowfish.inc');


/**
 * Determiner le mode test en fonction d'un define ou de la config
 * @param array $config
 * @return bool
 */
function axepta_is_sandbox($config){
	$test = false;
	// _AXEPTA_TEST force a TRUE pour utiliser l'adresse de test d'Axepta
	if ((defined('_AXEPTA_TEST') AND _AXEPTA_TEST)
		OR (isset($config['mode_test']) AND $config['mode_test'])){
		$test = true;
	}
	return $test;
}


/**
 * Determiner l'URL d'appel serveur en fonction de la config
 *
 * @param array $config
 * @return string
 */
function axepta_url_serveur($config){
	$host = 'https://paymentpage.axepta.bnpparibas/payssl.aspx';
	
	return $host;
}

