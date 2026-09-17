<?php
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\MdLoc;
    use apilib\AclManager;
    use apilib\ReqURI;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();

    $as_user_id = AclManager::managerSyUserChange($user, $params_raw); 
    $user_id = $as_user_id ? $as_user_id : $user[SyUser::$id];

    $db_res = ReadData::getUserTransportsList($db, $user_id);
    if (!$db_res) {
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
            APIMgmt::PRNT_UUID => $row[APIMgmt::PRNT_UUID],
        );
    }


    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $transports;
    echo json_encode($data);
    
?>