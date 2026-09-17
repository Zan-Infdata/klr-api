<?php

    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\SyUser;
    use apidb\SyUserAcl;
    use apilib\ReqURI;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)){
        include_once(ReqURI::get401FileName());
        exit();
    }

    $params_raw = ReqURI::getParams();

    $expected_params = array(APIMgmt::ID_KY);
    $params = APIMgmt::checkParams($params_raw, $expected_params);

    $new_user = SyUser::getSyUser($db, $params[APIMgmt::ID_KY]);

    if (is_null($new_user)) {
        APIMgmt::serverErrorResponse();
    }

    $out = array(
        APIMgmt::ID => $new_user[SyUser::$id],
        APIMgmt::NAME => $new_user[SyUser::$name],
        APIMgmt::SURNAME => $new_user[SyUser::$surr],
        APIMgmt::EMAIL => $new_user[SyUser::$email],
    );

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $out;
    echo json_encode($data);
    
?>