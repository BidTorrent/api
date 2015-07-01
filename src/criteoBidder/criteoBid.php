<?php
    $partner = '42';
    $endPoint = 'http://rtb.fr.eu.criteo.com/delivery/auction/request?profile=55';
    $privateKeyFile = 'keys/key-1-private.pem';
    
    function ReturnNoBid($error) {
        header("X-CriteoBidder-Error: $error");
        header("HTTP/1.0 204 No Content");
        die();
    }

    function Sign($price, $requestId, $publisherId) {
        Global $privateKeyFile;
        $key = file_get_contents($privateKeyFile);
        $data = number_format($price, 6).
                $requestId.
                $publisherId;
        openssl_sign($data, $result, $key);
        return base64_encode($result);
    }
    
    // UserMatching
    if (!isset($_COOKIE['Ids']))
    {
        ReturnNoBid("No Cookie UserID");
    }
    
    $ids = @unserialize($_COOKIE['Ids']);
        
    if ($ids == null || !is_array($ids) || !array_key_exists($partner, $ids))
    {
        ReturnNoBid("No Id for Criteo");
    }
    
    $userId = $ids[$partner];
    
    $content = json_decode(file_get_contents("php://input"), true);
    if ($content == null)
    {
        ReturnNoBid("Not able to read the json");
    }
    
    $bidtorrentPubId = $content['ext']['btid'];
    
    //Decode : simplify coding by removing undefined index notice.
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    $criteoRequest                                      = array();
    $criteoRequest['Analysis']                          = 1;
    $criteoRequest['PublisherID']                       = isset($content['site']) ? $content['site']['publisher']['id'] : $content['app']['publisher']['id'];
    $criteoRequest['Timeout']                           = 120;
    
    $criteoRequest['AppInfo']                           = array();
    $criteoRequest['AppInfo']['AppId']                  = $content['app']['publisher']['id'];
    $criteoRequest['AppInfo']['AppName']                = $content['app']['publisher']['name'];
    $criteoRequest['RequestID']                         = $content['id'];
    $criteoRequest['Device']                            = array();
    $criteoRequest['Device']['IdCategory']              = strtolower($content['device']['os']) == 'ios' ? 'IDFA' : 
                                                            strtolower($content['device']['os']) == 'android' ? 'ANDROID_ID' :
                                                            null;
    $criteoRequest['Device']['EnvironmentType']         = isset($content['site']) ? 0 : 1; // 0 => Web, 1 => In_app
    $criteoRequest['Device']['Id']                      = $content['device']['id'];
    $criteoRequest['Device']['OperatingSystemType']     = strtolower($content['device']['os']) == 'ios' ? 1 :
                                                            strtolower($content['device']['os']) == 'android' ? 2 :
                                                            0;
    $criteoRequest['User']                              = array();
    $criteoRequest['User']['CriteoUser']                = array();
    $criteoRequest['User']['CriteoUser']['Id']          = $userId;
    $criteoRequest['User']['CriteoUser']['Version']     = 1;
    $criteoRequest['User']['IpAddress']                 = $content['device']['ip'];
    $slot                                               = array();
    $slot['SlotId']                                     = 1;
    $slot['Intention']                                  = 0; //Accept
    $slot['RenderContainer']                            = 1; //Javascript
    $slot['Sizes']                                      = array(array('Item1' => $content['imp'][0]['banner']['h'], 'Item2' => $content['imp'][0]['banner']['w']));
    $criteoRequest['Slots']                             = array($slot);
    $criteoRequest['Currency']                          = $content['cur'];
    
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

    $context  = stream_context_create( $options );
    $result = file_get_contents($endPoint, false, $context);
    $criteoResponse = json_decode($result, true);

    if ($criteoResponse == null) {
        ReturnNoBid("No response from CRITEO");
    }

    if (!isset($criteoResponse['seatbid']) || count($criteoResponse['seatbid']) == 0) {
        ReturnNoBid("Criteo answered with no bid");
    }

    error_reporting(E_ALL);
    
    //echo json_encode($criteoResponse, JSON_PRETTY_PRINT);
    
    $response = array();
    $response['id'] = $criteoResponse['id'];
    $response['cur'] = $content['cur'];
    $seatbidObject = array(
        'id' => $criteoResponse['seatbid'][0]['bid'][0]['id'],
        'impid' => $criteoResponse['seatbid'][0]['bid'][0]['impid'],
        'price' => $criteoResponse['seatbid'][0]['bid'][0]['price'],
        'signature' => Sign($criteoResponse['seatbid'][0]['bid'][0]['price'], $criteoResponse['id'], $bidtorrentPubId),
        'nurl' => '',
        'adomain' => $criteoResponse['seatbid'][0]['bid'][0]['adomain'][0],
        'creative' => $criteoResponse['seatbid'][0]['bid'][0]['creative']['adm']
    );
    $seatbid = array();
    $seatbid['bid'] = array($seatbidObject);
    $response['seatbid'] = array($seatbid);
    
    echo json_encode($response, JSON_PRETTY_PRINT);
?>