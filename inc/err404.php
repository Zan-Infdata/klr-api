<?php

    use apilib\APIMgmt;

    http_response_code(404);

    $err = array();
    $err[APIMgmt::RC_KY] = 404;
    $err[APIMgmt::MSG_KY] = "404 - API reference not found" ;

    echo json_encode($err);

?>