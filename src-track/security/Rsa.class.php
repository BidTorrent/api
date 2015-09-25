<?php

class Rsa {
	function sign($clearTextData, $privateKey) {
		$result = null;
		openssl_sign($clearTextValue, $result, $privateKey);
		return $result;
	}
	
	function checkSignature($data, $signature, $publicKey) {
		return openssl_verify($data, $signature, $publicKey);
	}
}

?>