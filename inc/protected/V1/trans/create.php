<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\SyUserAcl;
    use apidb\MdLoc;
use apidb\MdLocStat;
use apidb\MdLocType;
    use apidb\SyUser;
    use apidb\Transaction;

    APIMgmt::checkMethod(["POST"]);
    if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
        APIMgmt::sendResponse(1, "Forbidden", 403);
    }
    $db = Db::connectDB();

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $data = $body_raw[APIMgmt::ELEM_KY];
    $toLocation_id = $body_raw[APIMgmt::PRNT_TO_KY];
    
    APIMgmt::checkStringParams($data, array(APIMgmt::UUID, APIMgmt::CODE, APIMgmt::NAME, APIMgmt::DESC));
    if (!isset($data[APIMgmt::ISACTIVE]) || !in_array($data[APIMgmt::ISACTIVE], array(0, 1, '0', '1'), true)
        || !APIMgmt::positiveId($toLocation_id)) { APIMgmt::badParamsResponse(); }
    $toLocation = MdLoc::getMdLoc($db, $toLocation_id);
    if (!$toLocation) { APIMgmt::badParamsResponse(); }

    $success = Transaction::createLocation($db, $data, $toLocation_id, MdLocType::TRANSPORT, MdLocStat::T_PARKED, $user[SyUser::$id]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>