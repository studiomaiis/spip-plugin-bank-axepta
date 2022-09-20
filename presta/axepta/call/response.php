<?php


if (!defined('_ECRIRE_INC_VERSION')){
	return;
}


include_spip('inc/bank');
include_spip('presta/axepta/inc/axepta');


function presta_axepta_call_response_dist($config, $response = null){

	$parametres = $parametres_hmac = array();
	$id_transaction = 0;
	$transaction_hash = $statut = '';
	$succes = false;
	
	$mode = $config['presta'];
	$config_id = bank_config_id($config);

	$Data = $_POST["Data"];
	$Len = $_POST["Len"];

	try {

		$myPayGate = new ctPaygate;

		$BlowfishPassword = $config['blowfish_password'];
		$HmacPassword = $config['hmac_password'];
		
		$TransID = $id_transaction;
		$RefNr = $transaction_hash;
		
		$plaintext = $myPayGate->ctDecrypt($Data, $Len, $BlowfishPassword);
		
		$parametres_necessaires = array(
			'PayID',
			'TransID',
			'mid',
			'Status',
			'Code'
		);

		$a = explode('&', $plaintext);
		
		if (is_array($a) and count($a)) {
			foreach ($a as $chaine) {
				if (preg_match('/=/', $chaine)) {
					list($cle, $valeur) = explode('=', $chaine, 2);
					$parametres[$cle] = $valeur;
					if (in_array($cle, $parametres_necessaires)) {
						$parametres_hmac[$cle] = $valeur;
					}
				}
			}
		}
		
		foreach ($parametres_necessaires as $cle) {
			if (!isset($parametres_hmac[$cle])) {
				throw new Exception('paramètre '.$cle.' manquant');
			}
		}
		
		$MAC = $myPayGate->ctHMAC($parametres_hmac['PayID'], $parametres_hmac['TransID'], $parametres_hmac['mid'], $parametres_hmac['Status'], $parametres_hmac['Code'], $HmacPassword);

		if (!isset($parametres['MAC']) and empty($parametres['MAC'])) {
			throw new Exception('paramètre MAC manquant');
		}
		
		$MAC = strtoupper($MAC);
		if ($MAC != $parametres['MAC']) {
			throw new Exception('signature MAC erronée '.$parametres_hmac['PayID'].'*'.$parametres_hmac['TransID'].'*'.$parametres_hmac['mid'].'*'.$parametres_hmac['Status'].'*'.$parametres_hmac['Code'].' '.$MAC.' ?= '.$parametres['MAC']);
		}

		$id_transaction = $parametres['TransID'];
		$transaction_hash = $parametres['UserData'];
		$statut = $parametres['Status'];
		$code = $parametres['Code'];
		
		if ($id_transaction and $transaction_hash) {
			
			if (!$row = sql_fetsel('*', 'spip_transactions', 'id_transaction='.intval($id_transaction).' AND transaction_hash='.sql_quote($transaction_hash))) {
				throw new Exception('transaction inconnue id_transaction='.$id_transaction.' transaction_hash='.$transaction_hash);
			}
			
			if ($row['reglee'] == 'oui') {
				return array($id_transaction, true);
			}
			
			$date = $_SERVER['REQUEST_TIME'];
			$date_paiement = sql_format_date(
				date('Y', $date), //annee
				date('m', $date), //mois
				date('d', $date), //jour
				date('H', $date), //Heures
				date('i', $date), //min
				date('s', $date) //sec
			);
			
			spip_log('call_response - ok statut='.$statut.' code='.$code, $mode._LOG_ERREUR);
				

			if ($code !== '00000000') {
				
				return bank_transaction_echec($id_transaction,
					array(
						'mode' => $mode,
						'config_id' => $config_id,
						'date_paiement' => $date_paiement,
						'code_erreur' => $code,
						'erreur' => $parametres['Description'],
					)
				);
				
			} else {
				
				$set = array(
					"pay_id" => $parametres['PayID'],
					"autorisation_id" => $parametres['XID'],
					"mode" => "$mode/$config_id",
					"montant_regle" => $row['montant'],
					"date_paiement" => $date_paiement,
					"statut" => 'ok',
					"reglee" => 'oui',
				);
				
				// todo card
				spip_log('call_response - parametres='.print_r($parametres, true), $mode);
				
				sql_updateq('spip_transactions', $set, 'id_transaction='.intval($id_transaction));
				spip_log('call_response - id_transaction='.$id_transaction.' reglée', $mode);
				
				$regler_transaction = charger_fonction('regler_transaction', 'bank');
				$regler_transaction($id_transaction, array('row_prec' => $row));
				
				return array($id_transaction, true);
					
			}
			
		}
		
	} catch (Exception $e) {
		
		spip_log('call_response - erreur='.$e->getMessage(), $mode._LOG_ERREUR);
		
		if (is_array($parametres) and count($parametres)) {
			spip_log('call_response - parametres='.print_r($parametres, true), $mode._LOG_ERREUR);
		} else {
			spip_log('call_response - $_REQUEST='.print_r($_REQUEST, true), $mode._LOG_ERREUR);
		}
		
	}

	return array(0, false);
	
}

