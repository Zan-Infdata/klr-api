<?php

use apidb\Db;
use apidb\MdLoc;
use apidb\ReadData;
use apidb\SyUserAcl;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('GET'));
if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
    APIMgmt::sendResponse(1, 'Forbidden', 403);
}
$warehouse = ReqURI::getParams()[APIMgmt::WAREHOUSE_ID] ?? null;
if (!APIMgmt::positiveId($warehouse)) { APIMgmt::badParamsResponse(); }

try {
    $db = Db::connectDB();
    if (!ReadData::isChecklistWarehouse(MdLoc::getMdLoc($db, $warehouse))) {
        APIMgmt::sendResponse(1, 'Warehouse unavailable', 409);
    }
    $stock = ReadData::getChecklistStock($db, $warehouse);
} catch (\Throwable $error) {
    APIMgmt::serverErrorResponse();
}
echo json_encode(array(APIMgmt::ERR_KY => 0, APIMgmt::DATA_KY => $stock));
