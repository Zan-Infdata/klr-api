<?php

use apidb\Db;
use apidb\ReadData;
use apidb\SyUserAcl;
use apilib\APIMgmt;
use apilib\ServiceActions;

APIMgmt::checkMethod(array('GET'));
ServiceActions::requireRole($user, SyUserAcl::MANAGER);
$db = Db::connectDB();
if (!$db) { APIMgmt::serverErrorResponse(); }
ServiceActions::sendData(ServiceActions::read(fn() => ReadData::getServiceLocations($db)));
