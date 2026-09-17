<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdLoc;
    use apidb\ReadData;
use apidb\SyUser;
use apidb\SyUserAcl;
    use apidb\TLocProp;
    use apilib\ReqURI;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)){
        include_once(ReqURI::get401FileName());
        exit();
    }

    $db_res = ReadData::getActiveSyUserList($db);

    if (is_null($db_res)) {
        APIMgmt::serverErrorResponse();
    }

    $users = array();
    foreach ($db_res as $row) {
        $users[] = array(
            APIMgmt::ID => $row[SyUser::$id],
            APIMgmt::NAME => $row[SyUser::$name],
            APIMgmt::SURNAME => $row[SyUser::$surr],
            APIMgmt::EMAIL => $row[SyUser::$email],
            APIMgmt::SELECTED => $row[SyUser::$id] == $user[SyUser::$id],
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $users;
    echo json_encode($data);
    
?>