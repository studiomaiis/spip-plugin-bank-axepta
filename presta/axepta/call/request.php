<?php


if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


include_spip('presta/axepta/inc/axepta');


/**
 * Generer le contexte pour le formulaire de requete de paiement
 * il faut avoir un id_transaction et un transaction_hash coherents
 * pour se premunir d'une tentative d'appel exterieur
 *
 * @param int $id_transaction
 * @param string $transaction_hash
 * @param $config
 *   configuration du module
 * @return array|false
 */
function presta_axepta_call_request_dist($id_transaction, $transaction_hash, $config){

	$mode = 'axepta';

	if (!is_array($config) OR !isset($config['type']) OR !isset($config['presta'])){
		spip_log("call_request : config invalide " . var_export($config, true), $mode . _LOG_ERREUR);
		return false;
	}

	$mode = $config['presta'];

	if (!$row = sql_fetsel("*", "spip_transactions", "id_transaction=" . intval($id_transaction) . " AND transaction_hash=" . sql_quote($transaction_hash))){
		spip_log("call_request : transaction $id_transaction / $transaction_hash introuvable", $mode . _LOG_ERREUR);
		return false;
	}

	// On peut maintenant connaître la devise et ses infos
	$devise = $row['devise'];
	$devise_info = bank_devise_info($devise);
	if (!$devise_info) {
		spip_log("Transaction #$id_transaction : la devise $devise n’est pas connue", $mode . _LOG_ERREUR);
		return false;
	}

	include_spip('inc/filtres');
	$contexte = array();

	$suffixe = '';
	if (axepta_is_sandbox($config)) {
		$suffixe = '_test';
	}

	$parametres_obligatoires = array(
		'merchant_id',
		'blowfish_password',
		'hmac_password',
	);
	foreach ($parametres_obligatoires as $cle) {
		if (!isset($config[$cle.$suffixe]) and empty($config[$cle.$suffixe])) {
			return false;
		}
	}

	$MerchantID = $config['merchant_id'.$suffixe];
	$MsgVer = "2.0";
	$TransID = $id_transaction;
	$RefNr = $transaction_hash;
$Amount = $row['montant'].'.23'; // todo
	$Amount = number_format($Amount, 2, ',', '');
	$Currency = $row['devise'];
	//$Capture = 'AUTO';
	$OrderDesc = '';
	if (axepta_is_sandbox($config) and $MerchantID == 'BNP_DEMO_AXEPTA') {
		$OrderDesc = 'Test:0000';
	}
	$URLNotify = bank_url_api_retour($config, "autoresponse");
	$URLBack = bank_url_api_retour($config, "cancel");
	$URLSuccess = bank_url_api_retour($config, "response");
	$URLFailure = bank_url_api_retour($config, "cancel");
	
	$HmacPassword = $config['hmac_password'.$suffixe];
	//$Response = 'encrypt';
	$BlowfishPassword = $config['blowfish_password'.$suffixe];
	
	
	// format data which is to be transmitted - required
	$pMsgVer = "MsgVer=$MsgVer";
	$pTransID = "TransID=$TransID";
	$pAmount = "Amount=$Amount";
	$pCurrency = "Currency=$Currency";
	$pRefNr = "RefNr=$RefNr";
	$pURLNotify = "URLNotify=$URLNotify";
	$pURLBack = "URLBack=$URLBack";
	$pURLSuccess = "URLSuccess=$URLSuccess";
	$pURLFailure = "URLFailure=$URLFailure";
	$pOrderDesc = "OrderDesc=$OrderDesc";
	//$pCapture = "Capture=$Capture";
	//$pResponse = "Response=$Response";
	
	//Creating MAC value
	$myPayGate = new ctPaygate;
	$MAC = $myPayGate->ctHMAC('', $TransID, $MerchantID, $Amount, $Currency, $HmacPassword);
	$pMAC = "MAC=$MAC";
	
	$query = array($MerchantID, $pMsgVer, $pTransID, $pRefNr, $pAmount, $pCurrency, $pURLNotify, $pURLBack, $pURLSuccess, $pURLFailure, $pMAC, $pOrderDesc);

	$plaintext = join("&", $query);
	$Len = strlen($plaintext);  // Length of the plain text string
	$Data = $myPayGate->ctEncrypt($plaintext, $Len, $BlowfishPassword);

	$hidden = '';
	$hidden.= '<input type="hidden" name="MerchantID" value="'.$MerchantID.'" />'."\n";
	$hidden.= '<input type="hidden" name="Data" value="'.$Data.'" />'."\n";
	$hidden.= '<input type="hidden" name="Len" value="'.$Len.'" />'."\n";

	include_spip('inc/filtres_mini');
	$contexte = array(
		'hidden' => $hidden,
		'action' => axepta_url_serveur($config),
		'backurl' => url_absolue(self()),
		'id_transaction' => $id_transaction,
		'transaction_hash' => $transaction_hash
	);

	return $contexte;
}

