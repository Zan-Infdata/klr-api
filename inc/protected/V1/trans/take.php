<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\Transaction;
    use apidb\MdLocStat;
use apilib\AclManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $transport_uuid = $body_raw[APIMgmt::ELEM_KY];


    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    $check = ReadData::canTakeTransport($db, $transport[MdLoc::$id]);
    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $success = Transaction::locationStatusChange($db, $transport[MdLoc::$id], $user_id, MdLocStat::T_PARKED, MdLocStat::T_TAKEN);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>