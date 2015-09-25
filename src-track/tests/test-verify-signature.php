<?php

$result = openssl_verify($_GET['in'], base64_decode($_GET['signature']), file_get_contents("../security/testKeys/key-1-public.pem"));

?>

<form method="GET" action="?">
	<input type="text" name="in" value="<?php echo $_GET['in'] ?>" />
	<input type="text" name="signature" value="<?php echo $_GET['signature'] ?>" />	
	<input type="submit" />
	<input type="text" value="<?php echo $result; ?>">
</form>