<?php

openssl_sign($_GET['in'], $signature, file_get_contents("../security/testKeys/key-1-private.pem"));

?>

<form method="GET" action="?">
	<input type="text" name="in" value="<?php echo $_GET['in'] ?>" />
	<input type="text" name ="out" value="<?php echo base64_encode($signature); ?>" />
	<input type="submit" />
</form>