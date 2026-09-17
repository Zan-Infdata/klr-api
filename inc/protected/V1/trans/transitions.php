<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEq;
    use apidb\MdLoc;
    use apidb\ReadData;
    use apidb\TEqProp;
    use apidb\TEqTrns;
use apidb\TLocTrns;

    APIMgmt::checkMethod(["GET"]);
    $db = Db::connectDB();

    $params_raw = ReqURI::getParams();

    $expected_params = array(APIMgmt::ELEM_KY);
    APIMgmt::checkRequiredParams($params_raw, $expected_params);

    $transport_uuid = $params_raw[APIMgmt::ELEM_KY];
    $date_from = urldecode($params_raw[APIMgmt::DATE_FROM_KY]) ?? null;
    $date_to = urldecode($params_raw[APIMgmt::DATE_TO_KY]) ?? null;

    $transport = MdLoc::getMdLocByUUID($db, $transport_uuid);
    $transport_transitions = ReadData::getTLocTrnsForTrans($db, $transport[MdLoc::$id], $date_from, $date_to);

    if(!$transport){   
        APIMgmt::badParamsResponse();
    }
    
    $history = array();
    foreach ($transport_transitions as $row) {
        $history[] = array(
            APIMgmt::ID => $row[TLocTrns::$id],
            APIMgmt::TIME => $row[TLocTrns::$time],
            APIMgmt::ISLAST => $row[TLocTrns::$isLast],
            APIMgmt::PRNT_FROM => $row[APIMgmt::PRNT_FROM_KY],
            APIMgmt::PRNT_TO => $row[APIMgmt::PRNT_TO_KY],
            APIMgmt::PRNT_FROM_NAME => $row[APIMgmt::PRNT_FROM_NAME_KY],
            APIMgmt::PRNT_TO_NAME => $row[APIMgmt::PRNT_TO_NAME_KY],
            APIMgmt::PRNT_FROM_TYPE => $row[APIMgmt::PRNT_FROM_TYPE_KY],
            APIMgmt::PRNT_TO_TYPE => $row[APIMgmt::PRNT_TO_TYPE_KY],
        );
    }

    $data = array();
    $data[APIMgmt::ERR_KY] = 0;
    $data[APIMgmt::DATA_KY] = $history;
    echo json_encode($data);
    
?>