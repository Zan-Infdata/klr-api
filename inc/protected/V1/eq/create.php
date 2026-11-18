<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\SyUserAcl;
    use apidb\MdEq;
    use apidb\MdEqType;
    use apidb\MdLoc;
    use apidb\MdLocType;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\TEqProp;
    use apidb\TLocProp;
    use apidb\Transaction;

    APIMgmt::checkMethod(["POST"]);
    if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
        APIMgmt::sendResponse(1, "Forbidden", 403);
    }
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::TYPE_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $data = $body_raw[APIMgmt::ELEM_KY];
    $toLocation_id = $body_raw[APIMgmt::PRNT_TO_KY];
    $type_id = $body_raw[APIMgmt::TYPE_KY];
    
    APIMgmt::checkStringParams($data, array(APIMgmt::UUID, APIMgmt::CODE, APIMgmt::NAME));
    if (!isset($data[APIMgmt::ISACTIVE]) || !in_array($data[APIMgmt::ISACTIVE], array(0, 1, '0', '1'), true)
        || !APIMgmt::positiveId($toLocation_id) || !APIMgmt::positiveId($type_id)) { APIMgmt::badParamsResponse(); }
    $toLocation = MdLoc::getMdLoc($db, $toLocation_id);
    if (!$toLocation || $toLocation[MdLoc::$typeId] != MdLocType::WAREHOUSE) { APIMgmt::badParamsResponse(); }
    if (!MdEqType::getMdEqType($db, $type_id)) { APIMgmt::badParamsResponse(); }

    $success = Transaction::createEquipment($db, $data, $toLocation_id, $type_id, $user[SyUser::$id]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>