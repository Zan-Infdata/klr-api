<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\ReadData;
    use apidb\MdLoc;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();


    $expected_params = array(APIMgmt::ID_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);


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
            APIMgmt::ISACTIVE => $row[MdEq::$isActive]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $equipment;
    echo json_encode($data);
    
?>