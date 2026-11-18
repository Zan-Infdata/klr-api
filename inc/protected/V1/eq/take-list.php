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

    $expected_params = array(APIMgmt::ELEM_LIST_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);
    foreach (array(APIMgmt::PRNT_FROM_KY, APIMgmt::PRNT_TO_KY) as $field) {
        if (!is_string($body_raw[$field]) || trim($body_raw[$field]) === '') { APIMgmt::badParamsResponse(); }
    }
    if (!is_array($body_raw[APIMgmt::ELEM_LIST_KY]) || !array_is_list($body_raw[APIMgmt::ELEM_LIST_KY]) || !ReqURI::isJsonList(APIMgmt::ELEM_LIST_KY)) {
        APIMgmt::badParamsResponse();
    }

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $successes = array();
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];

    foreach ($body_raw[APIMgmt::ELEM_LIST_KY] as $equipment_uuid ){
    
        $equipment = is_string($equipment_uuid) && trim($equipment_uuid) !== ''
            ? MdEq::getMdEqByUUID($db, $equipment_uuid) : false;
        $equipmentProp = $equipment ? TEqProp::getTEqPropByEqId($db, $equipment[MdEq::$id]) : false;
        $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
        $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);
    
        $check = $equipment && $equipmentProp && $fromLocation && $toLocation;
        $check = $check && ReadData::canTakeEquipment($db, $equipment[MdEq::$id], $user_id, $toLocation[MdLoc::$id]);
        $check = $check && $fromLocation[MdLoc::$id] == $equipmentProp[TEqProp::$locId];

        if (!$check){
            $success = false;
        }
        else {
            $success = Transaction::equipmentLocationChange($db, $equipment[MdEq::$id], $user_id, $equipmentProp[TEqProp::$locId], $toLocation[MdLoc::$id], 'take') === 200;
        }

        array_push($successes, $success ? 1 : 0);
    }



    $data = array();

    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $successes;

    echo json_encode($data);
    
?>