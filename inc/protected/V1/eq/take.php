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
    use apidb\TLocProp;
use apilib\AclManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $equipment_uuid = $body_raw[APIMgmt::ELEM_KY];
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];
    
    $equipment = MdEq::getMdEqByUUID($db, $equipment_uuid);
    $equipmentProp = TEqProp::getTEqPropByEqId($db,$equipment[MdEq::$id]);
    $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
    $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);
    $toLocationProp = TLocProp::getTLocPropByLocId($db, $toLocation[MdLoc::$id]);

    $check = ReadData::canTakeEquipment($db, $equipment[MdEq::$id]);
    $check = $check && $fromLocation[MdLoc::$id] == $equipmentProp[TEqProp::$locId];
    $check = $check && $fromLocation[MdLoc::$id] == $toLocationProp[TLocProp::$locIdPrnt];
    
    
    if (!$check){
        APIMgmt::badParamsResponse();
    }


    $success = Transaction::equipmentLocationChange($db, $equipment[MdEq::$id], $user_id, $equipmentProp[TEqProp::$locId], $toLocation[MdLoc::$id]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>