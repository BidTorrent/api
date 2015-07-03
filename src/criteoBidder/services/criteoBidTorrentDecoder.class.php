<?php

class CriteoBidTorrentDecoder
{
    var $bidfloor;
    var $helper;
    
    function __construct($helper) {
        $this->helper = $helper;
    }
    
    function tryDecode($stream, $userId, &$request, &$errorMessage) {
        $request = json_decode($stream, true);

        if ($request == null)
        {
            $errorMessage = 'Not able to read the json';
            return false;
        }
        
        $this->helper->Set($request, array('User', 'CriteoUser', 'Id'), $userId);
        $this->bidfloor = $this->helper->Get($request, array('imp', 0, 'bidfloor'));
        
        return true;
    }

    function tryEncode($rawResponse, &$response, &$errorMessage) {
        $response = json_decode($rawResponse, true);

        if ($response == null) {
            $errorMessage = "No response from CRITEO";
            return false;
        }

        if (!isset($response['seatbid']) || count($response['seatbid']) == 0) {
            $errorMessage = "Criteo answered with no bid";
            return false;
        }

        $price = $this->helper->Get($response, array('seatbid', 0, 'bid', 0, 'price'));
        $reqId = $this->helper->Get($response, array('id'));
        $btId = $this->helper->Get($response, array('ext', 'btid'));
        
        $this->helper->Set($response, array('seatbid', 0, 'bid', 0, 'signature'), $this->helper->Sign(
            $price, 
            $reqId, 
            $btId, 
            $this->bidfloor
            ));
        
        return true;
    }
}
?>