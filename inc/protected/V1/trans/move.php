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
    if (!is_array($body_raw)) { APIMgmt::badParamsResponse(); }

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);
    foreach (array(APIMgmt::ELEM_KY, APIMgmt::PRNT_FROM_KY, APIMgmt::PRNT_TO_KY) as $field) {
        if (!is_string($body_raw[$field]) || trim($body_raw[$field]) === '') { APIMgmt::badParamsResponse(); }
    }

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $transport_uuid = $body_raw[APIMgmt::ELEM_KY];
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];

    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    if (!$transport) { APIMgmt::badParamsResponse(); }
    $transportProp = TLocProp::getTLocPropByLocId($db, $transport[MdLoc::$id]);
    if (!$transportProp) { APIMgmt::badParamsResponse(); }
    $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
    $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);
    if (!$fromLocation || !$toLocation) { APIMgmt::badParamsResponse(); }
    
    $check = ReadData::canMoveTransport($db, $transport[MdLoc::$id]);
    $check = $check && $transportProp[TLocProp::$locIdPrnt] != $toLocation[MdLoc::$id];
    $check = $check && !MdLoc::isMdLocTransport($db, $toLocation[MdLoc::$id]);
    
    
    $checkUser = $transportProp[TLocProp::$userId] == $user[SyUser::$id] || SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER);

    $check = $check && $checkUser;


    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $status = Transaction::transportLocationChange($db, $transport[MdLoc::$id], $user_id, $fromLocation[MdLoc::$id], $toLocation[MdLoc::$id], $transportProp[TLocProp::$locStatId], $user);
    if ($status !== 200) { APIMgmt::sendResponse(1, $status === 409 ? "Movement conflict" : "Server error", $status); }
    
    $data = array();

    $data[APIMgmt::ERR_KY] = 1;
    echo json_encode($data);
    
?>