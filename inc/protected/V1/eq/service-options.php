<?php

use apidb\Db;
use apidb\ReadData;
use apilib\APIMgmt;
use apilib\ReqURI;
use apilib\ServiceActions;

APIMgmt::checkMethod(array('GET'));
$params = ReqURI::getParams();
APIMgmt::checkStringParams($params, array('transport', 'action'));
if (!in_array($params['action'], array('drop_off', 'pickup'), true)) { APIMgmt::badParamsResponse(); }
$db = Db::connectDB();
if (!$db) { APIMgmt::serverErrorResponse(); }
$userId = ServiceActions::effectiveUser($db, $user, $params);
$options = ServiceActions::read(fn() => ReadData::serviceTransportOptions($db, $params['transport'], $userId, $params['action'] === 'pickup'));
if ($options === null) { APIMgmt::sendResponse(1, 'Select a taken transport at a Service location', 409); }
ServiceActions::sendData($options);
