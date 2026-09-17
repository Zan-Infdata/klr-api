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

    $as_user_id = AclManager::managerSyUserChange($user, $body_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $expected_params = array(APIMgmt::ELEM_LIST_KY, APIMgmt::PRNT_TO_KY, APIMgmt::PRNT_FROM_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $successes = array();
    $fromLocation_uuid = $body_raw[APIMgmt::PRNT_FROM_KY];
    $toLocation_uuid = $body_raw[APIMgmt::PRNT_TO_KY];

    foreach ($body_raw[APIMgmt::ELEM_LIST_KY] as $equipment_uuid ){
    
        $equipment = MdEq::getMdEqByUUID($db, $equipment_uuid);
        $equipmentProp = TEqProp::getTEqPropByEqId($db,$equipment[MdEq::$id]);
        $fromLocation = MdLoc::getMdLocByUUID($db, $fromLocation_uuid);
        $fromLocationProp = TLocProp::getTLocPropByLocId($db, $fromLocation[MdLoc::$id]);
        $toLocation = MdLoc::getMdLocByUUID($db, $toLocation_uuid);

        $check = ReadData::canLeaveEquipment($db, $equipment[MdEq::$id], $user_id, $fromLocation[MdLoc::$id]);
        $check = $check && $fromLocation[MdLoc::$id] == $equipmentProp[TEqProp::$locId];
        $check = $check && $toLocation[MdLoc::$id] == $fromLocationProp[TLocProp::$locIdPrnt];


        if (!$check){
            $success = false;
        }
        else {
            $success = Transaction::equipmentLocationChange($db, $equipment[MdEq::$id], $user_id, $fromLocation[MdLoc::$id], $fromLocationProp[TLocProp::$locIdPrnt]);
            
            array_push($successes, $success ? 1 : 0);
        }

    }

    $data = array();

    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $successes;

    echo json_encode($data);
    
?>