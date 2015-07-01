<?php

    function Sign($price, $requestId, $publisherId) {
        $key = file_get_contents('keys/key-2-private.pem');
        $data = number_format($price, 6).
                $requestId.
                $publisherId;
        openssl_sign($data, $result, $key);
        return base64_encode($result);
    }
    
    include('services/decoder.class.php');
    
    $decoder = new Decoder('keys/key-2-private.pem');
    
    if (!$decoder->tryDecode(file_get_contents("php://input"), '', $criteoRequest, $errorMessage)) {
        ReturnNoBid($errorMessage);
    }
        
    $response = array(
        "id" => $criteoRequest['RequestID'],
        "cur" => "EUR",
        "seatbid" => array(
            array(
                "bid" => array(
                    array(
                        "id" => "559401bb8f9c6c9bb27e9d9d863e6df0",
                        "impid" => "1",
                        "price" => 0.032,
                        "signature" => Sign(0.032, $criteoRequest['RequestID'], $criteoRequest['ext']['btid']),
                        "nurl" => "",
                        "adomain" => "miniinthebox.com",
                        "creative" => "<script type='text\/javascript' src='http:\/\/cas.fr.eu.criteo.com\/delivery\/r\/ajs.php?did=559401bb8f9c6c9bb27e9d9d863e6df0&z=\${AUCTION_PRICE}&u=%7CI7o9XoAMMjl3HUcPVb3V2ZrBz3DszK8XlyOeSO5CqTc%3D%7C&c1=4z_1vBnVXyU3s5S1ODdcxBEhMh_1PE6dCt88-QtmDLG9BBdwzMTh2CtSDEVErFQDw4w7x8OFfnXnkfpEV-cFqznHSWR1Th9Q31_9KWeD80wN3wI2CEdDgi7v4Ymyl6qk367XHc2xzTvWq2fT0Fh_a3M0w1pLIwZfkpBcYSIkcj9D2K1RK8M4LPj0EAF8FbWARXwcOLtMQ4vEzV1rvzU6aEmZ_iSXY5uz&ct0=\${CLICK_URL}'><\/script>"
                    )
                )
            )
        )
    );
    
    echo json_encode($response, JSON_PRETTY_PRINT);
?>