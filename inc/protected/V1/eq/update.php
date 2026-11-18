<?php

    use apilib\ReqURI;
    use apilib\APIMgmt;
    use apidb\Db;
    use apidb\MdEqType;
    use apidb\SyUserAcl;
    use apidb\UpdateData;

    APIMgmt::checkMethod(array('POST'));
    if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
        APIMgmt::sendResponse(1, "Forbidden", 403);
    }
    $body_raw = ReqURI::getJsonBody();
    if (!is_array($body_raw)) { APIMgmt::badParamsResponse(); }
    APIMgmt::checkRequiredParams($body_raw, array(APIMgmt::ELEM_KY, APIMgmt::TYPE_KY));
    $data = $body_raw[APIMgmt::ELEM_KY];
    if (!is_array($data) || !UpdateData::isUpdateUUID($data[APIMgmt::UUID] ?? null)) { APIMgmt::badParamsResponse(); }
    foreach (array(APIMgmt::CODE, APIMgmt::NAME) as $field) {
        if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') { APIMgmt::badParamsResponse(); }
    }
    if (!isset($data[APIMgmt::ISACTIVE]) || !in_array($data[APIMgmt::ISACTIVE], array(0,1,'0','1'), true)) { APIMgmt::badParamsResponse(); }
    if ((!is_int($body_raw[APIMgmt::TYPE_KY]) && !is_string($body_raw[APIMgmt::TYPE_KY]))
        || filter_var($body_raw[APIMgmt::TYPE_KY], FILTER_VALIDATE_INT) === false || (int) $body_raw[APIMgmt::TYPE_KY] < 1) { APIMgmt::badParamsResponse(); }
    $db = Db::connectDB();
    if (!MdEqType::getMdEqType($db, $body_raw[APIMgmt::TYPE_KY])) { APIMgmt::badParamsResponse(); }
    $status = UpdateData::updateEqByUUID($db, $data[APIMgmt::UUID], $data[APIMgmt::CODE], $data[APIMgmt::NAME], $body_raw[APIMgmt::TYPE_KY], $data[APIMgmt::ISACTIVE]);
    if ($status !== 200) {
        $messages = array(400 => 'Bad request', 404 => 'Record not found', 409 => 'Ambiguous record', 500 => 'Server error');
        APIMgmt::sendResponse(1, $messages[$status] ?? 'Server error', $status);
    }
    // Preserve the existing single-item mutation contract used by web and mobile.
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(APIMgmt::ERR_KY => 1));
