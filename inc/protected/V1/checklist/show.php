<?php

use apidb\Db;
use apidb\ReadData;
use apidb\SyUser;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('GET'));
$id = ReqURI::getParams()[APIMgmt::ID_KY] ?? null;
if (!APIMgmt::positiveId($id)) { APIMgmt::badParamsResponse(); }

try {
    $checklist = ReadData::getAssignedChecklist(Db::connectDB(), (int) $user[SyUser::$id], $id);
} catch (\Throwable $error) {
    APIMgmt::serverErrorResponse();
}
APIMgmt::requireRecord($checklist);
echo json_encode(array(APIMgmt::ERR_KY => 0, APIMgmt::DATA_KY => $checklist));
