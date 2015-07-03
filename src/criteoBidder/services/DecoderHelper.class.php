<?php

class DecoderHelper {

    var $privateKeyFile;

    function __construct($privateKeyFile) {
        $this->privateKeyFile = $privateKeyFile;
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

    private function Sign($price, $requestId, $publisherId, $bidfloor) {
        $key = file_get_contents($this->privateKeyFile);
        $data = number_format($price, 6).
                $requestId.
                $publisherId.
                number_format($bidfloor, 6);
        openssl_sign($data, $result, $key);
        return base64_encode($result);
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
}

?>