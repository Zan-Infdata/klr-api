<?php

use apidb\Db;
use apidb\SyUser;
use apidb\SyUserAcl;
use apidb\Transaction;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('POST'));
if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
    APIMgmt::sendResponse(1, 'Forbidden', 403);
}
$body = ReqURI::getJsonBody();
$warehouse = $body[APIMgmt::WAREHOUSE_ID] ?? null;
$assignee = $body[APIMgmt::ASSIGNEE_ID] ?? null;
$lines = $body[APIMgmt::CHECKLIST_LINES] ?? null;
$validInteger = static function ($value) {
    return is_int($value) && $value > 0 && $value <= 2147483647;
};
if (!$validInteger($warehouse) || !$validInteger($assignee)
    || !$lines || !ReqURI::isJsonList(APIMgmt::CHECKLIST_LINES)) { APIMgmt::badParamsResponse(); }

foreach ($lines as &$line) {
    APIMgmt::checkRequiredParams($line, array('primary_type_id', 'quantity'));
    $line['secondary_type_id'] = $line['secondary_type_id'] ?? null;
    $line['equipment_id'] = $line['equipment_id'] ?? null;
    if (!$validInteger($line['primary_type_id']) || !$validInteger($line['quantity'])
        || ($line['secondary_type_id'] !== null && !$validInteger($line['secondary_type_id']))
        || ($line['equipment_id'] !== null && (!$validInteger($line['equipment_id'])
            || $line['secondary_type_id'] === null || $line['quantity'] !== 1))) {
        APIMgmt::badParamsResponse();
    }
}
unset($line);
APIMgmt::sendChecklistResult(Transaction::createChecklist(Db::connectDB(), (int) $user[SyUser::$id],
    $warehouse, $assignee, $lines));
