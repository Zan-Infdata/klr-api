<?php

use apidb\Db;
use apidb\SyUser;
use apidb\Transaction;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('POST'));
$body = ReqURI::getJsonBody();
APIMgmt::checkStringParams($body, array(APIMgmt::PRNT_FROM_KY, APIMgmt::PRNT_TO_KY));
$id = $body[APIMgmt::CHECKLIST_ID] ?? null;
$uuids = $body[APIMgmt::ELEM_LIST_KY] ?? null;
if (!is_int($id) || $id < 1 || !$uuids || !ReqURI::isJsonList(APIMgmt::ELEM_LIST_KY)) {
    APIMgmt::badParamsResponse();
}
foreach ($uuids as $uuid) {
    if (!APIMgmt::nonEmptyString($uuid)) { APIMgmt::badParamsResponse(); }
}
if (count(array_unique($uuids, SORT_STRING)) !== count($uuids)) { APIMgmt::badParamsResponse(); }

APIMgmt::sendChecklistResult(Transaction::takeWithChecklist(Db::connectDB(), (int) $user[SyUser::$id], $id,
    $body[APIMgmt::PRNT_FROM_KY], $body[APIMgmt::PRNT_TO_KY], $uuids));
