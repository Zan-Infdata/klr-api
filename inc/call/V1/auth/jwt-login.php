<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\ReadData;
    use apidb\UpdateData;
    use apidb\SyUser;
    use apilib\AuthManager;
    use apilib\JWTManager;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();
    

    $token = AuthManager::getTokenFromHeader();
    if(!$token){
        APIMgmt::sendResponse(1, "No token in header.", 401);
    }

    $decoded = JWTManager::verifyJWT($token);

    if (!$decoded || !APIMgmt::positiveId($decoded[APIMgmt::ID] ?? null)
        || !in_array($decoded[JWTManager::IS_REFRESH] ?? null, array(1, '1'), true)) {
        APIMgmt::sendResponse(1, "Wrong token.", 401);
    }

    $user = SyUser::getActiveSyUser($db, $decoded[APIMgmt::ID]);

    if (!$user) {
        APIMgmt::sendResponse(1, "Bad token.", 401);
    }

    if ($user[SyUser::$refreshToken] !== $token){
        APIMgmt::sendResponse(1, "Wrong refresh token.", 401);
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::MSG_KY] = "Success";
    echo json_encode($data);
    
?>