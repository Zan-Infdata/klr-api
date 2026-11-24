<?php

use apidb\Db;
use apidb\MdLoc;
use apidb\ReadData;
use apidb\SyUserAcl;
use apilib\APIMgmt;

APIMgmt::checkMethod(array('GET'));
if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)) {
    APIMgmt::sendResponse(1, 'Forbidden', 403);
}

try {
    $rows = ReadData::getTeleportDestinations(Db::connectDB());
} catch (\Throwable $error) {
    APIMgmt::serverErrorResponse();
}
if ($rows === null) { APIMgmt::serverErrorResponse(); }

$destinations = array();
foreach ($rows as $row) {
    $destinations[] = array(
        APIMgmt::ID => (int) $row[MdLoc::$id],
        APIMgmt::UUID => $row[MdLoc::$uuid],
        APIMgmt::NAME => $row[MdLoc::$name],
        APIMgmt::CODE => $row[MdLoc::$code],
        APIMgmt::TYPE_ID => (int) $row[MdLoc::$typeId],
    );
}
echo json_encode(array(APIMgmt::ERR_KY => 0, APIMgmt::DATA_KY => $destinations));
