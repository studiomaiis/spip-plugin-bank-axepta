<?php


if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * @param array $config
 * @param int $id_transaction
 * @param string $transaction_hash
 * @param array $options
 * @return array|string
 */
function presta_axepta_payer_acte_dist($config, $id_transaction, $transaction_hash, $options = array()){

	$call_request = charger_fonction('request', 'presta/axepta/call');
	$contexte = $call_request($id_transaction, $transaction_hash, $config);

	if (!$contexte){
		return '';
	}

	include_spip('inc/axepta');
	$contexte['sandbox'] = (axepta_is_sandbox($config) ? ' ' : '');
	$contexte['logo'] = bank_trouver_logo("axepta", "axepta.png");
	$contexte['config'] = $config;

	$contexte = array_merge($options, $contexte);

	return recuperer_fond('presta/axepta/payer/acte', $contexte);
}

