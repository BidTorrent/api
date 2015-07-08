<?php

$params = array();
initParam($params, 'price', 0.5);
initParam($params, 'publisher', 24252);
initParam($params, 'floor', 0.01);
initParam($params, 'auction', "auction" . mt_rand(10001,99999));
initParam($params, 'key', file_get_contents("../security/testKeys/key-1-private.pem"));

$data =
	number_format($params['price'], 6, ".", "") .
	$params['auction'] .
	$params['publisher'] .
	number_format($params['floor'], 6, ".", "");

openssl_sign($data, $result, $params['key']);

function initParam(&$params, $name, $default) {
	$params[$name] = $default;
	if (isset($_GET[$name])) 
		$params[$name] = $_GET[$name];
}

?>
<html>
<head>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
</head>
<body>
	<div class="container-fluid">
		<h1>Sign a BidTorrent bid with your private key</h1>
		<div class="row">
			<div class="col-sm-4">
				<form method="GET" action="?">
					<div class="form-group">
						<label>Price</label>	
						<input class="form-control" type="text" name="price" value="<?php echo $params['price'] ?>" placeholder="(eg. 0.841)" />		
					</div>
					<div class="form-group">
						<label>Auction</label>
						<input class="form-control" type="text" name="auction" value="<?php echo $params['auction'] ?>" placeholder="(eg. bc648ab16f51cab4f6ac5b1afb)" />		
					</div>
					<div class="form-group">
						<label>Publisher</label>
						<input class="form-control" type="text" name="publisher" value="<?php echo $params['publisher'] ?>" placeholder="(eg. 31542)" />		
					</div>
					<div class="form-group">
						<label>Floor</label>
						<input class="form-control" type="text" name="floor" value="<?php echo $params['floor'] ?>" placeholder="(eg. 0.05)" />		
					</div>
					<div class="form-group">
						<label>Private key (key-1)</label>
						<textarea class="form-control" name="key" cols="30" rows="10"><?php echo $params['key'] ?></textarea>
					</div>

					<div>		
						<input type="submit" />
					</div>
				</form>
			</div>
			<div class="col-sm-8">
				<div class="form-group">
					<label>Concatenated data</label>
					<input name="concat" type="text" class="form-control" value="<?php echo $data ?>" />
				</div>				
				<div class="form-group">
					<label>Signature (base64 encoded)</label>
					<textarea class="form-control"  name="result" id="" cols="30" rows="10"><?php echo base64_encode($result) ?></textarea>
				</div>				
			</div>
		</div>
	</div>
</body>
</html>
