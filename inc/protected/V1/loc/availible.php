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
    list($offset, $rowno) = APIMgmt::getPagination($params_raw);

    $expected_params = array(APIMgmt::RPP_KY, APIMgmt::PAGE_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);

    $fltr = APIMgmt::optionalString($params, APIMgmt::FILTER_KY);
    $sort_key = APIMgmt::sortKey($params);

    $includeService = ($params['include_service'] ?? null) === '1';
    $db_res = ReadData::getAvailibleLocationList($db, $sort_key, $fltr, $offset, $rowno, $includeService);

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
        if ($includeService) {
            $last = array_key_last($locations);
            $locations[$last][APIMgmt::TYPE_ID] = (int) $row[MdLoc::$typeId];
            $locations[$last]['pickupOnly'] = (bool) $row['pickupOnly'];
        }
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $locations;
    echo json_encode($data);
    
?>