<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEqTypePrimary;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $db_res = MdEqTypePrimary::getAll($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $types = array();
    foreach ($db_res as $row) {
        $types[] = array(
            APIMgmt::ID => $row[MdEqTypePrimary::$id],
            APIMgmt::NAME => $row[MdEqTypePrimary::$name],
            APIMgmt::CODE => $row[MdEqTypePrimary::$code]
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $types;
    echo json_encode($data);
    
?>