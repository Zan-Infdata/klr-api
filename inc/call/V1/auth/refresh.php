<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\ReadData;
    use apidb\SyUser;
    use apilib\JWTManager;
    use apilib\AuthManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();

    $token = AuthManager::getTokenFromHeader();
    if(!$token){
        APIMgmt::sendResponse(1, "No token in header.", 401);
    }
    $decoded = JWTManager::verifyJWT($token);

    if (!$decoded || !array_key_exists(APIMgmt::ID, $decoded)) {
        include_once(ReqURI::get401FileName());
        exit();
    }


    $user = ReadData::getSyUserByToken($db, $token);

    if (!$user || $user[SyUser::$refreshToken] !== $token) {
        APIMgmt::sendResponse(1, "Bad token.", 401);
    }

    $tokenData = array();
    $tokenData[APIMgmt::ID] = $user[SyUser::$id];
    $tokenData[JWTManager::IS_REFRESH] = 0;
    $tokenData[APIMgmt::NAME] = $user[SyUser::$name];
    $tokenData[APIMgmt::SURNAME] = $user[SyUser::$surr];
    $tokenData[APIMgmt::EMAIL] = $user[SyUser::$email];
    $tokenData[APIMgmt::ACL] = $user[SyUser::$aclId];

    $accessToken = JWTManager::createJWT($tokenData, JWTManager::ACCESS_EXPIRY);

    error_log("Granting new access token");

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::AC_TKN_KY] = $accessToken;
    echo json_encode($data);
    
?>