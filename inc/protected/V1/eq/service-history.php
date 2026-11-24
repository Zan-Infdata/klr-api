<?php

use apidb\Db;
use apidb\MdEq;
use apidb\ReadData;
use apilib\APIMgmt;
use apilib\ReqURI;
use apilib\ServiceActions;

APIMgmt::checkMethod(array('GET'));
$params = ReqURI::getParams();
APIMgmt::checkStringParams($params, array(APIMgmt::ELEM_KY));
$db = Db::connectDB();
if (!$db) { APIMgmt::serverErrorResponse(); }
$equipment = MdEq::getMdEqByUUID($db, $params[APIMgmt::ELEM_KY]);
APIMgmt::requireRecord($equipment);
ServiceActions::sendData(ServiceActions::read(fn() => ReadData::serviceHistory($db, $equipment[MdEq::$id])));
