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

    if (!$decoded || !array_key_exists(APIMgmt::ID, $decoded) || !$decoded[JWTManager::IS_REFRESH]) {
        APIMgmt::sendResponse(1, "Wrong token.", 401);
    }

    $user = SyUser::getSyUser($db, $decoded[APIMgmt::ID]);

    if (!$user) {
        APIMgmt::sendResponse(1, "User not found.", 404);
    }

    if ($user[SyUser::$refreshToken] !== $token){
        APIMgmt::sendResponse(1, "Wrong refresh token.", 401);
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::MSG_KY] = "Success";
    echo json_encode($data);
    
?>