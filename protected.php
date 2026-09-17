<?php

    use apilib\ReqURI;
    use apilib\AuthManager;
    use apidb\Db;
    use apidb\SyUser;
    use apilib\APIMgmt;
    use apilib\JWTManager;

    // READ API KY FROM REQUEST
    $token = AuthManager::getTokenFromHeader();
    if(!$token){
        APIMgmt::sendResponse(1, "No token in header.", 401);
    }
    $decoded = JWTManager::verifyJWT($token);

    // CHECK API KY
    if (!$decoded || !array_key_exists(APIMgmt::ID, $decoded) || !array_key_exists(JWTManager::IS_REFRESH, $decoded)) {
        // API KY not found
        include_once(ReqURI::get401FileName());
        exit();
    }
    // check if it is refresh token
    if ($decoded[JWTManager::IS_REFRESH] == 1) {
        // API KY not found
        APIMgmt::sendResponse(1, "Bad token - do not use refresh token for normal API calls", 400);
    }


    $db = Db::connectDB();
    $user = SyUser::getSyUser($db, $decoded[APIMgmt::ID]);

    if (!$user) {
        // User not found
        include_once(ReqURI::get401FileName());
        exit();
    }

    if (ReqURI::isFileName()) {
        // INCLUDE found FILE
        include_once(ReqURI::getFileName());
    }
    else {
        // API reference not found
        include_once(ReqURI::get404FileName());
    } // else



?>
