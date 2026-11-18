<?php

    use apilib\APIMgmt;
    use apilib\AuthManager;
    use apilib\Environment;

    if (Environment::isProduction()) {
        APIMgmt::sendResponse(1, "Not found", 404);
    }


    $user = AuthManager::getUserFromToken();
    

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::MSG_KY] = $user;
    echo json_encode($data);
    
?>