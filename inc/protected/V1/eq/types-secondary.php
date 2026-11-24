<?php

    use apilib\APIMgmt;
    use apilib\ReqURI;
    use apidb\Db;
    use apidb\MdEqTypePrimary;
    use apidb\MdEqTypeSecondary;

    APIMgmt::checkMethod(["GET"]);
    $primaryTypeId = ReqURI::getParams()[APIMgmt::PRIMARY_TYPE_KY] ?? null;
    if (!APIMgmt::positiveId($primaryTypeId)) { APIMgmt::badParamsResponse(); }

    $db = Db::connectDB();
    try {
        APIMgmt::requireRecord(MdEqTypePrimary::getMdEqTypePrimary($db, $primaryTypeId));
        $rows = MdEqTypeSecondary::getByPrimaryType($db, $primaryTypeId);
    } catch (\Throwable $error) {
        APIMgmt::serverErrorResponse();
    }
    if ($rows === null) { APIMgmt::serverErrorResponse(); }

    $types = array();
    foreach ($rows as $row) {
        $types[] = array(
            APIMgmt::ID => (int) $row[MdEqTypeSecondary::$id],
            APIMgmt::CODE => $row[MdEqTypeSecondary::$code],
            APIMgmt::NAME => $row[MdEqTypeSecondary::$name],
        );
    }
    echo json_encode(array(APIMgmt::ERR_KY => 0, APIMgmt::DATA_KY => $types));
