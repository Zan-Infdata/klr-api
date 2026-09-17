<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdEqType;
    use apidb\MdLoc;
use apidb\MdLocType;
use apidb\ReadData;
    use apidb\TEqProp;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $db_res = ReadData::getExtendedEquipmentList($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $types = array();
    foreach ($db_res as $row) {
        
        $types[] = array(
            APIMgmt::ID => $row[MdEq::$id],
            APIMgmt::UUID => $row[MdEq::$uuid],
            APIMgmt::NAME => $row[MdEq::$name],
            APIMgmt::CODE => $row[MdEq::$code],
            APIMgmt::ISACTIVE => $row[MdEq::$isActive],
            APIMgmt::PRNT => array(
                APIMgmt::NAME => $row[MdLoc::$name],
                APIMgmt::IS_WRH => $row[MdLoc::$typeId] == MdLocType::WAREHOUSE,
            ),
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $types;
    echo json_encode($data);
    
?>