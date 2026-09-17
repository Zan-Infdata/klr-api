<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\ReadData;
    use apidb\UpdateData;
    use apidb\SyUser;
    use apilib\JWTManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();
    

    $post = ReqURI::getJsonBody();
    if (!isset($post)) {
        APIMgmt::badParamsResponse();
    }
    

    $expected_params = array(APIMgmt::PWD_KY,APIMgmt::USR_KY);
    APIMgmt::checkRequiredParams($post, $expected_params);


    $pwd = $post[APIMgmt::PWD_KY];
    $username = $post[APIMgmt::USR_KY]; 

    $user = ReadData::getSyUserByName($db, $username);

    if (!$user) {
        APIMgmt::sendResponse(1, "User not found.", 404);
    }

    $hash = $user[SyUSer::$pwd];
    $valid = password_verify($pwd, $hash);

    if (!$valid) {
        APIMgmt::sendResponse(1, "Invalid password.", 401);
    }


    $tokenData = array();
    $tokenData[APIMgmt::ID] = $user[SyUser::$id];
    $tokenData[JWTManager::IS_REFRESH] = 0;
    $tokenData[APIMgmt::NAME] = $user[SyUser::$name];
    $tokenData[APIMgmt::SURNAME] = $user[SyUser::$surr];
    $tokenData[APIMgmt::EMAIL] = $user[SyUser::$email];
    $tokenData[APIMgmt::ACL] = $user[SyUser::$aclId];

    $accessToken = JWTManager::createJWT($tokenData, JWTManager::ACCESS_EXPIRY);

    $tokenData[JWTManager::IS_REFRESH] = 1;
    $refreshToken = JWTManager::createJWT($tokenData, JWTManager::REFRESH_EXPIRY);

    
    $success = UpdateData::setRefreshToken($db, $user[SyUser::$id], $refreshToken);
    if (!$success) {
        APIMgmt::sendResponse(1, $success, 500);
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::AC_TKN_KY] = $accessToken;
    $data[APIMgmt::RE_TNK_KY] = $refreshToken;
    echo json_encode($data);
    
?>