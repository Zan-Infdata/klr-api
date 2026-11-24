<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\ReadData;
    use apidb\MdLoc;
    use apidb\TEqProp;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();


    $expected_params = array(APIMgmt::ID_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);
    if (!APIMgmt::positiveId($params[APIMgmt::ID_KY])) { APIMgmt::badParamsResponse(); }


    $location = MdLoc::getMdLoc($db, $params[APIMgmt::ID_KY]);
    APIMgmt::requireRecord($location);

    $db_res = ReadData::getAvailibleEquipmentOnLocation($db, $params[APIMgmt::ID_KY]);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $equipment = array();
    foreach ($db_res as $row) {
        $equipment[] = array(
            APIMgmt::ID => $row[MdEq::$id],
            APIMgmt::NAME => $row[MdEq::$name],
            APIMgmt::CODE => $row[MdEq::$code],
            APIMgmt::UUID => $row[MdEq::$uuid],
            APIMgmt::ISACTIVE => $row[MdEq::$isActive],
            APIMgmt::TYPE_ID => $row[MdEq::$primaryTypeId],
            APIMgmt::SECONDARY_TYPE_ID => (int) $row[MdEq::$secondaryTypeId],
            APIMgmt::SERVICE_STATUS_ID => (int) $row[TEqProp::$serviceStatId]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $equipment;
    echo json_encode($data);
    
?>