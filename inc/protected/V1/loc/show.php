<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
    use apidb\TEqProp;
use apidb\TLocProp;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($params_raw, $expected_params);

    $loc_uuid = $params_raw[APIMgmt::ELEM_KY];
    
    $location = MdLoc::getMdLocByUUID($db, $loc_uuid);
    $locationProp = TLocProp::getTLocPropByLocId($db, $location[MdLoc::$id]);
    $prntLocation = MdLoc::getMdLoc($db, $locationProp[TLocProp::$locIdPrnt]);

    $locData = array();
    
    $locData[APIMgmt::ID] = $location[MdLoc::$id];
    $locData[APIMgmt::NAME] = $location[MdLoc::$name];
    $locData[APIMgmt::CODE] = $location[MdLoc::$code];
    $locData[APIMgmt::TYPE_ID] = $location[MdLoc::$typeId];
    $locData[APIMgmt::DESC] = $location[MdLoc::$desc];
    $locData[APIMgmt::STATUS_ID] = $locationProp[TLocProp::$locStatId];
    $locData[APIMgmt::ISACTIVE] = $location[MdLoc::$isActive];
    $locData[APIMgmt::PRNT_ID] = $prntLocation[MdLoc::$id];
    $locData[APIMgmt::PRNT_NAME] = $prntLocation[MdLoc::$name];
    $locData[APIMgmt::PRNT_UUID] = $prntLocation[MdLoc::$uuid];


    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $locData;
    echo json_encode($data);
    
?>