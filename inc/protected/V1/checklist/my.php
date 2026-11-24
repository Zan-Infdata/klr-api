<?php

use apidb\Db;
use apidb\ReadData;
use apidb\SyUser;
use apilib\APIMgmt;
use apilib\ReqURI;

APIMgmt::checkMethod(array('GET'));
$params = APIMgmt::checkParams(ReqURI::getParams(), array(APIMgmt::WAREHOUSE_ID, 'open_only'));
$warehouse = $params[APIMgmt::WAREHOUSE_ID];
$openOnly = $params['open_only'];
if (($warehouse !== null && !APIMgmt::positiveId($warehouse))
    || ($openOnly !== null && $openOnly !== '1')) { APIMgmt::badParamsResponse(); }

try {
    $rows = ReadData::getAssignedChecklists(Db::connectDB(), (int) $user[SyUser::$id], $warehouse, $openOnly === '1');
} catch (\Throwable $error) {
    APIMgmt::serverErrorResponse();
}
echo json_encode(array(APIMgmt::ERR_KY => 0, APIMgmt::DATA_KY => $rows));
