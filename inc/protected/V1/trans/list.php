<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\TLocProp;
    use apidb\SyUser;
    use apidb\MdLocStat;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $db_res = ReadData::getActiveTransportsList($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $types = array();
    foreach ($db_res as $row) {
        $types[] = array(
            APIMgmt::ID => $row[MdLoc::$id],
            APIMgmt::NAME => $row[MdLoc::$name],
            APIMgmt::CODE => $row[MdLoc::$code],
            APIMgmt::UUID => $row[MdLoc::$uuid],
            APIMgmt::PRNT_ID => $row[TLocProp::$locIdPrnt],
            APIMgmt::STATUS_ID => $row[TLocProp::$locStatId],
            APIMgmt::PRNT_NAME => $row[APIMgmt::PRNT_NAME],
            APIMgmt::USER_KY => $row[TLocProp::$locStatId] == MdLocStat::T_TAKEN && isset($row[SyUser::$id])
                ? array(
                    APIMgmt::ID => $row[SyUser::$id],
                    APIMgmt::NAME => $row[SyUser::$name],
                    APIMgmt::SURNAME => $row[SyUser::$surr],
                )
                : null
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $types;
    echo json_encode($data);
    
?>