<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $db_res = ReadData::getActiveSitesList($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $types = array();
    foreach ($db_res as $row) {
        $types[] = array(
            APIMgmt::ID => $row[MdLoc::$id],
            APIMgmt::NAME => $row[MdLoc::$name],
            APIMgmt::CODE => $row[MdLoc::$code],
            APIMgmt::UUID => $row[MdLoc::$uuid]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $types;
    echo json_encode($data);
    
?>