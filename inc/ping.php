<?php

    use apilib\APIMgmt;

    http_response_code(200);

    $err = array();
    $err[APIMgmt::RC_KY] = 0;
    $err[APIMgmt::MSG_KY] = "PONG - looks like it works" ;

    echo json_encode($err);

?>