<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\Transaction;
    use apidb\TEqProp;
    use apilib\AclManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();
    if (!is_array($body_raw)) { APIMgmt::badParamsResponse(); }

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);
    foreach (array(APIMgmt::PRNT_FROM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::ELEM_KY) as $field) {
        if (!is_string($body_raw[$field]) || trim($body_raw[$field]) === '') { APIMgmt::badParamsResponse(); }
    }

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $equipment_uuid = $body_raw[APIMgmt::ELEM_KY];
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];
    
    $equipment = is_string($equipment_uuid) && trim($equipment_uuid) !== ''
        ? MdEq::getMdEqByUUID($db, $equipment_uuid) : false;
    $equipmentProp = $equipment ? TEqProp::getTEqPropByEqId($db, $equipment[MdEq::$id]) : false;
    $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
    $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);

    $check = $equipment && $equipmentProp && $fromLocation && $toLocation;
    $check = $check && ReadData::canTakeEquipment($db, $equipment[MdEq::$id], $user_id, $toLocation[MdLoc::$id]);
    $check = $check && $fromLocation[MdLoc::$id] == $equipmentProp[TEqProp::$locId];
    
    
    if (!$check){
        APIMgmt::badParamsResponse();
    }


    $status = Transaction::equipmentLocationChange($db, $equipment[MdEq::$id], $user_id, $equipmentProp[TEqProp::$locId], $toLocation[MdLoc::$id], 'take');
    if ($status !== 200) { APIMgmt::sendResponse(1, $status === 409 ? "Movement conflict" : "Server error", $status); }
    $data = array();

    $data[APIMgmt::ERR_KY] = 1;
    echo json_encode($data);
    
?>