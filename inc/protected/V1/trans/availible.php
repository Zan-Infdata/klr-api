<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\MdLoc;
    use apilib\JWTManager;
    use apilib\AuthManager;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();


    $expected_params = array(APIMgmt::FILTER_KY, APIMgmt::RPP_KY, APIMgmt::PAGE_KY, APIMgmt::SORT_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);
    $offset = ($params[APIMgmt::PAGE_KY] - 1) * $params[APIMgmt::RPP_KY];


    $db_res = ReadData::getAvailibleTransportsList($db, $params[APIMgmt::SORT_KY], $params[APIMgmt::FILTER_KY], $offset, $params[APIMgmt::RPP_KY]);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $transports = array();
    foreach ($db_res as $row) {
        $transports[] = array(
            APIMgmt::ID => $row[MdLoc::$id],
            APIMgmt::NAME => $row[MdLoc::$name],
            APIMgmt::CODE => $row[MdLoc::$code],
            APIMgmt::UUID => $row[MdLoc::$uuid],
            APIMgmt::ISACTIVE => $row[MdLoc::$isActive],
            APIMgmt::PRNT_ID => $row[APIMgmt::PRNT_ID],
            APIMgmt::PRNT_NAME => $row[APIMgmt::PRNT_NAME],
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $transports;
    echo json_encode($data);
    
?>