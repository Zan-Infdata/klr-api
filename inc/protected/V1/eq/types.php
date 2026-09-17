<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEqType;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $db_res = MdEqType::getAll($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $types = array();
    foreach ($db_res as $row) {
        $types[] = array(
            APIMgmt::ID => $row[MdEqType::$id],
            APIMgmt::NAME => $row[MdEqType::$name],
            APIMgmt::CODE => $row[MdEqType::$code]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $types;
    echo json_encode($data);
    
?>