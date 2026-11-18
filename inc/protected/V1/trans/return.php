<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\Transaction;
    use apidb\MdLocStat;
    use apidb\SyUserAcl;
    use apidb\TLocProp;
use apilib\AclManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();
    if (!is_array($body_raw)) { APIMgmt::badParamsResponse(); }

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);
    foreach (array(APIMgmt::ELEM_KY) as $field) {
        if (!is_string($body_raw[$field]) || trim($body_raw[$field]) === '') { APIMgmt::badParamsResponse(); }
    }

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $transport_uuid = $body_raw[APIMgmt::ELEM_KY];

    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    if (!$transport) { APIMgmt::badParamsResponse(); }
    $transportProp = TLocProp::getTLocPropByLocId($db, $transport[MdLoc::$id]);
    if (!$transportProp) { APIMgmt::badParamsResponse(); }
    
    $checkUser = $transportProp[TLocProp::$userId] == $user[SyUser::$id] || SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER);
    
    $check = ReadData::canReturnTransport($db, $transport[MdLoc::$id]);
    $check = $check && $checkUser;

    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $status = Transaction::locationStatusChange($db, $transport[MdLoc::$id], $user_id, MdLocStat::T_TAKEN, MdLocStat::T_PARKED, $user);
    if ($status !== 200) { APIMgmt::sendResponse(1, $status === 409 ? "Movement conflict" : "Server error", $status); }
    $data = array();

    $data[APIMgmt::ERR_KY] = 1;
    echo json_encode($data);
    
?>