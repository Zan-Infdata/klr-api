<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\SyUser;


    $params_raw = ReqURI::getParams();

    
    $pwd = $params_raw["gsl"];
    $hash = password_hash($pwd, PASSWORD_DEFAULT);

    $data = array();
    $data[APIMgmt::RC_KY] = 0;
    $data[APIMgmt::MSG_KY] = $hash;

    http_response_code(200);
    echo json_encode($data);

?>