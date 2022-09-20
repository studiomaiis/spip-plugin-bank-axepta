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
	if (isset($config['merchant_id']) and (preg_match('/_t$/', $config['merchant_id']) or $config['merchant_id'] == 'BNP_DEMO_AXEPTA')) {
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

