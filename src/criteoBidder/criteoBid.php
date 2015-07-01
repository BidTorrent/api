<?php
    $endPoint = 'http://rtb.fr.eu.criteo.com/delivery/auction/request?profile=55';
    
    header("Access-Control-Allow-Origin: *");

    function ReturnNoBid($error) {
        header("X-CriteoBidder-Error: $error");
        header("HTTP/1.0 204 No Content");
        die();
    }

    include('services\userMatch.class.php');
    include('services\decoder.class.php');
    
    $decoder = new Decoder('keys/key-1-private.pem');
    $userResolver = new UserResolver();
    
    $userId = $userResolver->getUserId($_COOKIE);
    
    header("X-CriteoBidder-UserId: $userId");
    
    if (!$decoder->tryDecode(file_get_contents("php://input"), $userId, $criteoRequest, $errorMessage)) {
        ReturnNoBid($errorMessage);
    }
    
    $bidRequest = json_encode(array('bidrequest' => $criteoRequest));

    header("X-CriteoBidder-Request: $bidRequest");
    
    $options = array(
      'http' => array(
        'method'  => 'POST',
        'content' => $bidRequest,
        'header'=>  "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
        )
    );

    $context  = stream_context_create($options);
    $rawResponse = file_get_contents($endPoint, false, $context);
    
    header("X-CriteoBidder-Response: $rawResponse");
    
    if (!$decoder->tryEncode($rawResponse, $response, $errorMessage)) {
        ReturnNoBid($errorMessage);
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
?>