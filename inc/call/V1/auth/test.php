<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apilib\Environment;
    use apidb\SyUser;


    if (Environment::isProduction()) { APIMgmt::sendResponse(1, "Not found", 404); }
    APIMgmt::checkMethod(array('POST'));
    $params_raw = ReqURI::getJsonBody();

    
    APIMgmt::checkStringParams($params_raw, array("gsl"));
    $pwd = $params_raw["gsl"];
    if (strpos($pwd, "\0") !== false) { APIMgmt::badParamsResponse(); }
    $hash = password_hash($pwd, PASSWORD_DEFAULT);

    $data = array();
    $data[APIMgmt::RC_KY] = 0;
    $data[APIMgmt::MSG_KY] = $hash;

    http_response_code(200);
    echo json_encode($data);

?>