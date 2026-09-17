<?php

    use apilib\APIMgmt;

    http_response_code(401);

    $err = array();
    $err[APIMgmt::RC_KY] = 401;
    $err[APIMgmt::MSG_KY] = "401 - Unauthorized API call" ;

    echo json_encode($err);

?>