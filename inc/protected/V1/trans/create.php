<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
use apidb\MdLocStat;
use apidb\MdLocType;
    use apidb\SyUser;
    use apidb\Transaction;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();
    //TODO: check permissions to create

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $data = $body_raw[APIMgmt::ELEM_KY];
    $toLocation_id = $body_raw[APIMgmt::PRNT_TO_KY];
    
    $toLocation = MdLoc::getMdLoc($db, $toLocation_id);


    $check = isset($data[APIMgmt::UUID]) && !empty($data[APIMgmt::UUID]);
    $check = $check && isset($data[APIMgmt::CODE]) && !empty($data[APIMgmt::CODE]);
    $check = $check && isset($data[APIMgmt::NAME]) && !empty($data[APIMgmt::NAME]);
    $check = $check && isset($data[APIMgmt::DESC]) && !empty($data[APIMgmt::DESC]);

    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $success = Transaction::createLocation($db, $data, $toLocation_id, MdLocType::TRANSPORT, MdLocStat::T_PARKED, $user[SyUser::$id]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>