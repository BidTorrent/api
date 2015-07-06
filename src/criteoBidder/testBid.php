<?php
    
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    
    include('services/decoderHelper.class.php');
    include('services/criteoBidTorrentDecoder.class.php');
    include('services/userResolver.class.php');
    include('services/testBidder.class.php');
    include('services/wrapperBidder.class.php');
    
    $helper = new DecoderHelper('keys/key-1-private.pem');
    $decoder = new CriteoBidTorrentDecoder($helper);
    $userResolver = new UserResolver();
    $innerBidder = new TestBidder();
    
    $bidder = new WrapperBidder(
        $userResolver,
        $decoder,
        $innerBidder
    );
    
    $request = json_decode(file_get_contents("php://input"), true);
    $response = $bidder->GetResponse($request);
    echo json_encode($response, JSON_PRETTY_PRINT);
    
?>