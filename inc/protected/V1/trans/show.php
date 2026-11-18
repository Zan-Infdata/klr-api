<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
use apidb\TLocProp;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkStringParams($params_raw, $expected_params);

    $transport_uuid = $params_raw[APIMgmt::ELEM_KY];
    
    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    APIMgmt::requireRecord($transport);
    $transportProp = TLocProp::getTLocPropByLocId($db, $transport[MdLoc::$id]);
    APIMgmt::requireRecord($transportProp, 409);
    $prntLocation = MdLoc::getMdLoc($db, $transportProp[TLocProp::$locIdPrnt]);
    APIMgmt::requireRecord($prntLocation, 409);

    
    $trnsData = array();
    
    $trnsData[APIMgmt::ID] = $transport[MdLoc::$id];
    $trnsData[APIMgmt::UUID] = $transport[MdLoc::$uuid];
    $trnsData[APIMgmt::NAME] = $transport[MdLoc::$name];
    $trnsData[APIMgmt::CODE] = $transport[MdLoc::$code];
    $trnsData[APIMgmt::DESC] = $transport[MdLoc::$desc];
    $trnsData[APIMgmt::TYPE_ID] = $transport[MdLoc::$typeId];
    $trnsData[APIMgmt::ISACTIVE] = $transport[MdLoc::$isActive];
    $trnsData[APIMgmt::PRNT_ID] = $prntLocation[MdLoc::$id];
    $trnsData[APIMgmt::PRNT_NAME] = $prntLocation[MdLoc::$name];
    $trnsData[APIMgmt::PRNT_UUID] = $prntLocation[MdLoc::$uuid];

        

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $trnsData;
    echo json_encode($data);
    
?>