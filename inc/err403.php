<?php

    use apilib\APIMgmt;

    http_response_code(403);

    $err = array();
    $err[APIMgmt::RC_KY] = 403;
    $err[APIMgmt::MSG_KY] = "403 - Not valid API call" ;

    echo json_encode($err);

?>