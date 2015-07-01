<?php

class Decoder
{
    var $privateKeyFile;
    var $currency;
    var $bidtorrentId;

    function __construct($privateKeyFile) {
        $this->privateKeyFile = $privateKeyFile;
    }

    function tryDecode($stream, $userId, &$request, &$errorMessage) {
        $content = json_decode(file_get_contents("php://input"), true);

        if ($content == null)
        {
            $errorMessage = 'Not able to read the json';
            return false;
        }

        $criteoRequest                                      = array();
        $criteoRequest['Analysis']                          = 1;
        $criteoRequest['PublisherID']                       = isset($content['site']) ? $content['site']['publisher']['id'] : $content['app']['publisher']['id'];
        $criteoRequest['Timeout']                           = 120;

        $criteoRequest['AppInfo']                           = array();
        $criteoRequest['AppInfo']['AppId']                  = $this->Get($content, array('app', 'publisher', 'id'));
        $criteoRequest['AppInfo']['AppName']                = $this->Get($content, array('app', 'publisher', 'name'));
        $criteoRequest['RequestID']                         = $content['id'];
        $criteoRequest['Device']                            = array();
        $criteoRequest['Device']['IdCategory']              = strtolower($this->Get($content, array('device', 'os'))) == 'ios' ? 'IDFA' : 
                                                                strtolower($this->Get($content, array('device', 'os'))) == 'android' ? 'ANDROID_ID' :
                                                                null;
        $criteoRequest['Device']['EnvironmentType']         = isset($content['site']) ? 0 : 1; // 0 => Web, 1 => In_app
        $criteoRequest['Device']['Id']                      = $this->Get($content, array('device', 'id'));
        $criteoRequest['Device']['OperatingSystemType']     = strtolower($this->Get($content, array('device', 'os'))) == 'ios' ? 1 :
                                                                strtolower($this->Get($content, array('device', 'os'))) == 'android' ? 2 :
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
        $criteoRequest['ext']['btid']                       = $content['ext']['btid'];

        $request = $criteoRequest;

        $this->currency = $content['cur'];
        $this->bidtorrentId = $content['ext']['btid'];
        return true;
    }

    function tryEncode($rawResponse, &$response, &$errorMessage) {

        $criteoResponse = json_decode($rawResponse, true);

        if ($criteoResponse == null) {
            $errorMessage = "No response from CRITEO";
            return false;
        }

        if (!isset($criteoResponse['seatbid']) || count($criteoResponse['seatbid']) == 0) {
            $errorMessage = "Criteo answered with no bid";
            return false;
        }

        $response = array();
        $response['id'] = $criteoResponse['id'];
        $response['cur'] = $this->currency;
        $seatbidObject = array(
            'id' => $criteoResponse['seatbid'][0]['bid'][0]['id'],
            'impid' => $criteoResponse['seatbid'][0]['bid'][0]['impid'],
            'price' => $criteoResponse['seatbid'][0]['bid'][0]['price'],
            'signature' => $this->Sign($criteoResponse['seatbid'][0]['bid'][0]['price'], $criteoResponse['id'], $this->bidtorrentId),
            'nurl' => '',
            'adomain' => $criteoResponse['seatbid'][0]['bid'][0]['adomain'][0],
            'creative' => $criteoResponse['seatbid'][0]['bid'][0]['creative']['adm']
        );
        $seatbid = array();
        $seatbid['bid'] = array($seatbidObject);
        $response['seatbid'] = array($seatbid);

        return true;
    }

    function Get($obj, $keys) {
        if (!is_array($keys))
            return $this->Get($obj, array($keys));

        if (count($keys) == 0)
            return $obj;

        $currentKey = array_shift($keys);
        if (!isset($obj[$currentKey]))
            return null;

        return $this->Get($obj[$currentKey], $keys);
    }

    function Set(&$obj, $keys, $value) {
        if ($value == null)
            return;
        $current &= $obj;
        $lastKey = array_pop($keys);
        foreach($keys as $key) {
            if (!isset($current[$key]))
                $current[$key] = array();
            $current &= $current[$key];
        }
        $current[$lastKey] = $value;
    }

    private function Sign($price, $requestId, $publisherId) {
        $key = file_get_contents($this->privateKeyFile);
        $data = number_format($price, 6).
                $requestId.
                $publisherId;
        openssl_sign($data, $result, $key);
        return base64_encode($result);
    }
}
?>