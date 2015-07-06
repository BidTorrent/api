<?php

class CriteoBidder {

    var $endPoint;
    
    function __construct($endPoint) {
        $this->endPoint = $endPoint;
    }
    
    function GetResponse($request) {
        header("X-CriteoBidder-Request: $request");
        
        $options = array(
          'http' => array(
            'method'  => 'POST',
            'content' => $request,
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );

        $context  = stream_context_create($options);
        $rawResponse = file_get_contents($this->endPoint, false, $context);
        
        header("X-CriteoBidder-Response: $rawResponse");
    }
}

?>