<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\UpdateData;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();
    //TODO: check permissions to create

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $data = $body_raw[APIMgmt::ELEM_KY];

    $check = isset($data[APIMgmt::UUID]) && !empty($data[APIMgmt::UUID]);
    $check = $check && isset($data[APIMgmt::CODE]) && !empty($data[APIMgmt::CODE]);
    $check = $check && isset($data[APIMgmt::NAME]) && !empty($data[APIMgmt::NAME]);
    $check = $check && isset($data[APIMgmt::DESC]) && !empty($data[APIMgmt::DESC]);
    
    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $success = UpdateData::updateLocByUUID($db, $data[APIMgmt::UUID], $data[APIMgmt::CODE], $data[APIMgmt::NAME], $data[APIMgmt::DESC], $data[APIMgmt::ISACTIVE]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>