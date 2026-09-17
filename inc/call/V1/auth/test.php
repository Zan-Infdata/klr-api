<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\SyUser;


    $pwd = "testing";
    $hash = password_hash($pwd, PASSWORD_DEFAULT);

    $data = array();
    $data[APIMgmt::RC_KY] = 0;
    $data[APIMgmt::MSG_KY] = $_SESSION[SyUser::$id];

    http_response_code(200);
    echo json_encode($data);

?>