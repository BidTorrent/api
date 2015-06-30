<?php

class Rsa {
	function Encrypt($clearTextValue, $publicKey) 
	{

	}
	
	function Decrypt($clearTextValue, $privateKey) {
		
	}

	function Sign($clearTextData, $privateKey) {
		$result = null;
		openssl_sign($clearTextValue, $result, $privateKey);
		return $result;
	}
	
	function checkSignature($cipheredData, $signature, $publicKey) {
		return openssl_verify($cipheredData, $signature, $publicKey);
	}
}

?>