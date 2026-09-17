<?php

    use apilib\APIMgmt;
    use apilib\AuthManager;


    $user = AuthManager::getUserFromToken();
    

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::MSG_KY] = $user;
    echo json_encode($data);
    
?>