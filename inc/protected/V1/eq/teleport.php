<?php

use apidb\Db;
use apidb\SyUserAcl;
use apidb\Transaction;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('POST'));
if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
    APIMgmt::sendResponse(1, 'Forbidden', 403);
}

$body = ReqURI::getJsonBody();
APIMgmt::checkStringParams($body, array(APIMgmt::ELEM_KY, APIMgmt::PRNT_FROM_KY, APIMgmt::PRNT_TO_KY));
$db = Db::connectDB();
if (!$db) { APIMgmt::serverErrorResponse(); }
$status = Transaction::teleportEquipment($db, $user, $body[APIMgmt::ELEM_KY],
    $body[APIMgmt::PRNT_FROM_KY], $body[APIMgmt::PRNT_TO_KY]);

if ($status !== 200) {
    $messages = array(403 => 'Forbidden', 404 => 'Record not found', 409 => 'Movement conflict');
    APIMgmt::sendResponse(1, $messages[$status] ?? 'Server error', $status);
}

echo json_encode(array(APIMgmt::ERR_KY => 0));
