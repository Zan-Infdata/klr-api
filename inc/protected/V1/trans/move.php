<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\SyUserAcl;
    use apidb\Transaction;
    use apidb\TLocProp;
    use apilib\AclManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $transport_uuid = $body_raw[APIMgmt::ELEM_KY];
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];

    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    $transportProp = TLocProp::getTLocPropByLocId($db, $transport[MdLoc::$id]);
    $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
    $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);
    
    $check = ReadData::canMoveTransport($db, $transport[MdLoc::$id]);
    $check = $check && $transportProp[TLocProp::$locIdPrnt] != $toLocation[MdLoc::$id];
    $check = $check && !MdLoc::isMdLocTransport($db, $toLocation[MdLoc::$id]);
    
    
    $checkUser = $transportProp[TLocProp::$userId] == $user[SyUser::$id] || SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER);

    $check = $check && $checkUser;


    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $success = Transaction::transportLocationChange($db, $transport[MdLoc::$id], $user_id, $transportProp[TLocProp::$locIdPrnt], $toLocation[MdLoc::$id], $transportProp[TLocProp::$locStatId]);
    
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>