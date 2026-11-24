<?php

use apidb\Db;
use apidb\MdLoc;
use apidb\MdLocType;
use apidb\ReadData;
use apidb\SyUserAcl;
use apilib\APIMgmt;
use apilib\ReqURI;
use apilib\ServiceActions;

APIMgmt::checkMethod(array('GET'));
ServiceActions::requireRole($user, SyUserAcl::MANAGER);
$id = ReqURI::getParams()[APIMgmt::ID] ?? null;
if ($id !== null && !APIMgmt::positiveId($id)) { APIMgmt::badParamsResponse(); }
$db = Db::connectDB();
if (!$db) { APIMgmt::serverErrorResponse(); }
if ($id !== null) {
    $location = MdLoc::getMdLoc($db, $id);
    if (!$location || (int) $location[MdLoc::$typeId] !== MdLocType::SERVICE) {
        APIMgmt::sendResponse(1, 'Service location not found', 404);
    }
}
ServiceActions::sendData(ServiceActions::read(fn() => ReadData::getServiceTypeOptions($db, (int) $id)));
