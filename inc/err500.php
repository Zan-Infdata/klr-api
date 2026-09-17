<?php

    use apilib\APIMgmt;

    http_response_code(500);

    $err = array();
    $err[APIMgmt::RC_KY] = 500;
    $err[APIMgmt::MSG_KY] = "500 - Error processing API call" ;

    echo json_encode($err);

?>