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

    $expected_params = array(APIMgmt::RPP_KY, APIMgmt::PAGE_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);
    $offset = ($params[APIMgmt::PAGE_KY] - 1) * $params[APIMgmt::RPP_KY];

    $fltr = $params[APIMgmt::FILTER_KY] ?? null;
    $sort_key = $params[APIMgmt::SORT_KY] ?? 1;

    $db_res = ReadData::getAvailibleLocationList($db, $sort_key, $fltr, $offset, $params[APIMgmt::RPP_KY]);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $locations = array();
    foreach ($db_res as $row) {
        $locations[] = array(
            APIMgmt::ID => $row[MdLoc::$id],
            APIMgmt::NAME => $row[MdLoc::$name],
            APIMgmt::CODE => $row[MdLoc::$code],
            APIMgmt::UUID => $row[MdLoc::$uuid],
            APIMgmt::DESC => $row[MdLoc::$desc],
            APIMgmt::ISACTIVE => $row[MdLoc::$isActive]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $locations;
    echo json_encode($data);
    
?>