<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
    use apidb\TEqProp;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($params_raw, $expected_params);

    $equipment_uuid = $params_raw[APIMgmt::ELEM_KY];
    
    $equipment = MdEq::getMdEqByUUID($db, $equipment_uuid);
    $equipmentProp = TEqProp::getTEqPropByEqId($db,$equipment[MdEq::$id]);
    $prntLocation = MdLoc::getMdLoc($db, $equipmentProp[TEqProp::$locId]);

    if(!$equipment){   
        APIMgmt::badParamsResponse();
    }
    
    $eqData = array();
    
    $eqData[APIMgmt::ID] = $equipment[MdEq::$id];
    $eqData[APIMgmt::UUID] = $equipment[MdEq::$uuid];
    $eqData[APIMgmt::NAME] = $equipment[MdEq::$name];
    $eqData[APIMgmt::CODE] = $equipment[MdEq::$code];
    $eqData[APIMgmt::TYPE_ID] = $equipment[MdEq::$typeId];
    $eqData[APIMgmt::ISACTIVE] = $equipment[MdEq::$isActive];
    $eqData[APIMgmt::PRNT_ID] = $prntLocation[MdLoc::$id];
    $eqData[APIMgmt::PRNT_NAME] = $prntLocation[MdLoc::$name];
    $eqData[APIMgmt::PRNT_UUID] = $prntLocation[MdLoc::$uuid];

        

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $eqData;
    echo json_encode($data);
    
?>