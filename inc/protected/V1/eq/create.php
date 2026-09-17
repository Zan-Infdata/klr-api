<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
    use apidb\MdLocType;
    use apidb\ReadData;
    use apidb\SyUser;
    use apidb\TEqProp;
    use apidb\TLocProp;
    use apidb\Transaction;

    APIMgmt::checkMethod(["POST"]);
    $db = Db::connectDB();
    //TODO: check permissions to create

    $body_raw = ReqURI::getJsonBody();

    $expected_params = array(APIMgmt::ELEM_KY, APIMgmt::PRNT_TO_KY, APIMgmt::TYPE_KY);
    APIMgmt::checkRequiredParams($body_raw, $expected_params);

    $data = $body_raw[APIMgmt::ELEM_KY];
    $toLocation_id = $body_raw[APIMgmt::PRNT_TO_KY];
    $type_id = $body_raw[APIMgmt::TYPE_KY];
    
    $toLocation = MdLoc::getMdLoc($db, $toLocation_id);

    $check = in_array($toLocation[MdLoc::$typeId], [MdLocType::WAREHOUSE]);
    $check = $check && isset($data[APIMgmt::UUID]) && !empty($data[APIMgmt::UUID]);
    $check = $check && isset($data[APIMgmt::CODE]) && !empty($data[APIMgmt::CODE]);
    $check = $check && isset($data[APIMgmt::NAME]) && !empty($data[APIMgmt::NAME]);

    if (!$check){
        APIMgmt::badParamsResponse();
    }

    $success = Transaction::createEquipment($db, $data, $toLocation_id, $type_id, $user[SyUser::$id]);
    $data = array();

    $data[APIMgmt::ERR_KY] = $success ? 1 : 0;
    echo json_encode($data);
    
?>