<?php
/* -----------------
 * PHP apiclass
 * @author: Žan Pusenjak
 * @date: 31.01.2024
 * 
 * PHP classes for DB objects with PDO
 * 
 */

    namespace apidb;

use apilib\APIMgmt;
use \PDO;
	use \PDOException;



    class Db {
	
		protected const API_CNF_FILE = "db.conf";

		protected const CONFIG_SERVER = "server";
		protected const CONFIG_DBNAME = "dbname";
		protected const CONFIG_PORT = "port";
		protected const CONFIG_USER = "u";
		protected const CONFIG_PWD = "p";


	    protected static $conn;
		protected static $err;


		public static function connectDB() {  

			if ( !isset(self::$conn) ) {
				
				$config = parse_ini_file(self::API_CNF_FILE, true);
				$conString = "mysql:host=".$config[self::CONFIG_SERVER].";dbname=".$config[self::CONFIG_DBNAME].";port=".$config[self::CONFIG_PORT];

				try {

					self::$conn = new PDO($conString , $config[self::CONFIG_USER], $config[self::CONFIG_PWD]) ;
					
					// if USING PDOException
					//self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					self::$err = false;
				}
				catch (PDOException $e) {
					error_log("Database connection error: " . $e->getMessage());
					
					self::$err = true;
				}
			}
			return self::$conn;

		} // connectDB


		public static function getConnError() {

			$connErr = "No connection!";
			if (isset(self::$conn)) {
				$connErr = self::$conn->errorInfo() ;
			}

			return $connErr ;
			
		} // getConnError



		public static function getConnErrNo() {

			$connErrNo = "9999";
			if (isset(self::$conn)) {
				$connErrNo = self::$conn->errorCode() ;
			}

			return $connErrNo ;
			
		} // getConnErrNo

		
		public static function isConnErr() {

			return self::$err;

		} // isConnErr

		
	}  // ApiDb


	class SQLPaging{

		private $RPP = 30;
		private $MAXP = 0;


		function __construct($rpp, $cnt) {
			$this->RPP = $rpp;
			// $this->CNT = $cnt;

			$this->MAXP = ceil($cnt/$rpp);
		} // __construct



		public function getOffset($p) {

			$start = ($p > $this->MAXP) ? $this->RPP * (max($this->MAXP - 1,0)) : $this->RPP * ($p-1) ;
			return $start ;

		} // getOffset

		public function getRPP() {

			return $this->RPP ;

		} // getRPP

	}  // SQLPaging


	/* 
    *  Class APIList - manages preparation of different data record lists with our without SUM line
    *
	*/
	class ReadData {

		const SORTCOL_ID = 1;
		const SORTCOL_CODE = 2;
		const SORTCOL_NAME = 3;
		const SORTCOL_PARM1 = 4;
		const SORTCOL_PARM2 = 5;
		const SORTCOL_PARM3 = 6;
		const SORTCOL_PARM4 = 7;
		const SORTCOL_PARM5 = 8;
		const SORTCOL_PARM6 = 9;
		const SORTCOL_FKID = 10;
		const SORTCOL_FKNAME = 11;
		const SORTCOL_FKCODE = 12;
		

		public static function getActiveSyUserList($db) {


			$qry =  " SELECT ";
			$qry .= "        u.".SyUser::$id;
			$qry .= "       ,u.".SyUser::$name;
			$qry .= "       ,u.".SyUser::$surr;
			$qry .= "       ,u.".SyUser::$email;

			$qry .= " FROM ";
			$qry .= "        ".SyUser::$table." as u" ;

			$qry .= " WHERE ";
			$qry .= "        u.".SyUser::$isActive." = 1" ;


			$statement = $db->prepare($qry);
			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;
		} // getActiveTransportsList


	
		public static function getSyUserByName($db, $name) {
			$qry = " SELECT ";
			$qry .= "        s.".SyUser::$id;
			$qry .= "       ,s.".SyUser::$name;
			$qry .= "       ,s.".SyUser::$surr;
			$qry .= "       ,s.".SyUser::$pwd;
			$qry .= "       ,s.".SyUser::$email;
			$qry .= "       ,s.".SyUser::$aclId;

			$qry .= " FROM ";
			$qry .= "        ".SyUser::$table." as s" ;
			$qry .= " WHERE ";
			$qry .= "        s.".SyUser::$name." = :n" ;
			$qry .= " AND ";
			$qry .= "        s.".SyUser::$isActive." = 1" ;


			$statement = $db->prepare($qry);
			
			$statement->bindParam(":n", $name, PDO::PARAM_STR);

			$statement->execute();
			
			$res = $statement->fetch(PDO::FETCH_ASSOC);
			return $res;

		} // getSyUserByName


		public static function getSyUserByToken($db, $token) {
			$qry = " SELECT ";
			$qry .= "        s.".SyUser::$id;
			$qry .= "       ,s.".SyUser::$name;
			$qry .= "       ,s.".SyUser::$surr;
			$qry .= "       ,s.".SyUser::$pwd;
			$qry .= "       ,s.".SyUser::$email;
			$qry .= "       ,s.".SyUser::$refreshToken;
			$qry .= "       ,s.".SyUser::$aclId;

			$qry .= " FROM ";
			$qry .= "        ".SyUser::$table." as s" ;
			$qry .= " WHERE ";
			$qry .= "        s.".SyUser::$refreshToken." = :t" ;
			$qry .= " AND ";
			$qry .= "        s.".SyUser::$isActive." = 1" ;


			$statement = $db->prepare($qry);
			
			$statement->bindParam(":t", $token, PDO::PARAM_STR);

			$statement->execute();
			
			$res = $statement->fetch(PDO::FETCH_ASSOC);
			return $res;

		} // getSyUserByName
	

		public static function getExtendedEquipmentList($db) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;
			$qry .= "       ,e.".MdEq::$name;
			$qry .= "       ,e.".MdEq::$code;
			$qry .= "       ,e.".MdEq::$uuid;
			$qry .= "       ,e.".MdEq::$isActive;
			$qry .= "       ,l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$typeId;

			$qry .= " FROM ";
			$qry .= "        ".MdEq::$table." as e," ;
			$qry .= "        ".TEqProp::$table." as ep," ;
			$qry .= "        ".MdLoc::$table." as l" ;

			$qry .= " WHERE ";
			$qry .= "        e.".MdEq::$id." = ep.".TEqProp::$equipmentId." " ;
			$qry .= " AND    l.".MdLoc::$id." = ep.".TEqProp::$locId." " ;
			$qry .= " AND    e.".MdEq::$id." > 1";



			$statement = $db->prepare($qry);

			$success = $statement->execute();

			if (!$success){
				return null;
			}

			$res = array();
			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			return $res;

		} // getAvailibleTransportsList

		public static function getAvailibleEquipmentOnLocation($db, $lid) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;
			$qry .= "       ,e.".MdEq::$name;
			$qry .= "       ,e.".MdEq::$code;
			$qry .= "       ,e.".MdEq::$uuid;
			$qry .= "       ,e.".MdEq::$isActive;
			$qry .= "       ,e.".MdEq::$typeId;

			$qry .= " FROM ";
			$qry .= "        ".MdEq::$table." as e," ;
			$qry .= "        ".TEqProp::$table." as ep" ;

			$qry .= " WHERE ";
			$qry .= "        e.".MdEq::$isActive." = 1 " ;
			$qry .= " AND    e.".MdEq::$id." = ep.".TEqProp::$equipmentId." " ;
			$qry .= " AND    ep.".TEqProp::$locId."  = :lid" ;
			$qry .= " AND    e.".MdEq::$id." > 1";



			$statement = $db->prepare($qry);

			$statement->bindParam(":lid", $lid, PDO::PARAM_INT);

			$success = $statement->execute();

			if (!$success){
				return null;
			}

			$res = array();
			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			return $res;

		} // getAvailibleTransportsList

		public static function getAvailibleTransportsList($db, $ordr, $f, $offset, $rowno) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$isActive;
			$qry .= "       ,prnt.".MdLoc::$id." as ".APIMgmt::PRNT_ID;
			$qry .= "       ,prnt.".MdLoc::$name." as ".APIMgmt::PRNT_NAME;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp," ;
			$qry .= "        ".MdLoc::$table." as prnt" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_PARKED." " ;
			$qry .= " AND    l.".MdLoc::$id." > 1";


			if( !is_null($f)){
				$qry .= " AND    l.".MdLoc::$code." LIKE :f " ;
			}

			$qry .= " ORDER BY ";

			switch ($ordr) {
				case self::SORTCOL_ID:
					$qry .= "    l.".MdLoc::$id;
					break;
				case self::SORTCOL_CODE:
					$qry .= "    l.".MdLoc::$code;
					break;
				case self::SORTCOL_NAME:
					$qry .= "    l.".MdLoc::$name;
					break;
			} // switch

			$qry .= " LIMIT ".$offset.", ".$rowno ;

			$statement = $db->prepare($qry);

			if( !is_null($f)){
				// prepare LIKE string for search
				$fval = "%".str_replace(" ","%",$f)."%"; 
				$statement->bindParam(":f", $fval, PDO::PARAM_STR);
			}

			$success = $statement->execute();

			if (!$success){
				return null;
			}

			$res = array();
			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			return $res;

		} // getAvailibleTransportsList

		public static function getAvailibleLocationList($db, $ordr, $f, $offset, $rowno) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$desc;
			$qry .= "       ,l.".MdLoc::$isActive;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp " ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::L_OPEN." " ;
			$qry .= " AND    l.".MdLoc::$id." > 1";

			if( !is_null($f)){
				$qry .= " AND    l.".MdLoc::$code." LIKE :f " ;
			}

			$qry .= " ORDER BY ";

			switch ($ordr) {
				case self::SORTCOL_ID:
					$qry .= "    l.".MdLoc::$id;
					break;
				case self::SORTCOL_CODE:
					$qry .= "    l.".MdLoc::$code;
					break;
				case self::SORTCOL_NAME:
					$qry .= "    l.".MdLoc::$name;
					break;
			} // switch

			$qry .= " LIMIT ".$offset.", ".$rowno ;

			$statement = $db->prepare($qry);

			if( !is_null($f)){
				// prepare LIKE string for search
				$fval = "%".str_replace(" ","%",$f)."%"; 
				$statement->bindParam(":f", $fval, PDO::PARAM_STR);
			}
			

			$success = $statement->execute();

			if (!$success){
				return null;
			}

			$res = array();
			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			return $res;

		} // getAvailibleLocationList


		public static function getUserTransportsList($db, $uid) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$isActive;
			$qry .= "       ,prnt.".MdLoc::$id." as ".APIMgmt::PRNT_ID;
			$qry .= "       ,prnt.".MdLoc::$name." as ".APIMgmt::PRNT_NAME;
			$qry .= "       ,prnt.".MdLoc::$uuid." as ".APIMgmt::PRNT_UUID;
			
			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp," ;
			$qry .= "        ".MdLoc::$table." as prnt" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN." " ;
			$qry .= " AND    lp.".TLocProp::$userId." = :uid " ;
			$qry .= " AND    l.".MdLoc::$id." > 1";


			$statement = $db->prepare($qry);

			$statement->bindParam(":uid", $uid, PDO::PARAM_INT);

			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;


		} // getAvailibleTransportsList

		public static function getActiveWarehouseList($db) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$isActive;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." > 1";


			$statement = $db->prepare($qry);
			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;
		} // getActiveWarehouseList

		public static function getActiveSitesList($db) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$isActive;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." > 1";


			$statement = $db->prepare($qry);
			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;
		} // getActiveSitesList

		public static function getActiveTransportsList($db) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$isActive;
			$qry .= "       ,lp.".TLocProp::$locIdPrnt;
			$qry .= "       ,lp.".TLocProp::$locStatId;
			$qry .= "       ,prnt.".MdLoc::$name." as ".APIMgmt::PRNT_NAME;
			$qry .= "       ,u.".SyUser::$id;
			$qry .= "       ,u.".SyUser::$name;
			$qry .= "       ,u.".SyUser::$surr;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l" ;
			$qry .= " JOIN ".TLocProp::$table." as lp ON l.".MdLoc::$id." = lp.".TLocProp::$locId;
			$qry .= " LEFT JOIN ".MdLoc::$table." as prnt ON prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt;
			$qry .= " LEFT JOIN ".SyUser::$table." as u ON u.".SyUser::$id." = lp.".TLocProp::$userId;
			$qry .= " AND lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." > 1";


			$statement = $db->prepare($qry);
			if (!$statement->execute()) {
				return null;
			}

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;
		} // getActiveTransportsList

		public static function getUserTransportsReturnList($db, $uid) {

			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$isActive;
			$qry .= "       ,prnt.".MdLoc::$id." as ".APIMgmt::PRNT_ID;
			$qry .= "       ,prnt.".MdLoc::$name;
			$qry .= "       ,prnt.".MdLoc::$uuid." as ".APIMgmt::PRNT_UUID;
			$qry .= "       ,count(ep.".TEqProp::$id.") as ".APIMgmt::COUNT_CHILD;
			
			$qry .= " FROM ";
			$qry .= "       (".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp," ;
			$qry .= "        ".MdLoc::$table." as prnt )" ;
			$qry .= " LEFT OUTER JOIN ";
			$qry .= "        ".TEqProp::$table." as ep" ;
			$qry .= " ON ";
			$qry .= "        ep.".TEqProp::$locId." = l.".MdLoc::$id." " ;
			
			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    prnt.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN." " ;
			$qry .= " AND    lp.".TLocProp::$userId." = :uid " ;
			$qry .= " AND    l.".MdLoc::$id." > 1";
			
			$qry .= " GROUP BY ";
			$qry .= "        l.".MdLoc::$id." " ;

			$statement = $db->prepare($qry);

			$statement->bindParam(":uid", $uid, PDO::PARAM_INT);

			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;


		} // getUserTransportsReturnList

		public static function getTEqTrnsWithLoc($db, $eid, $df, $dt) {

			$qry =  " SELECT ";
			$qry .= "        t.".TEqTrns::$id;
			$qry .= "       ,t.".TEqTrns::$time;
			$qry .= "       ,t.".TEqTrns::$isLast;
			$qry .= "       ,lf.".MdLoc::$uuid." as ".APIMgmt::PRNT_FROM_KY;
			$qry .= "       ,lt.".MdLoc::$uuid." as ".APIMgmt::PRNT_TO_KY;
			$qry .= "       ,lf.".MdLoc::$name." as ".APIMgmt::PRNT_FROM_NAME_KY;
			$qry .= "       ,lt.".MdLoc::$name." as ".APIMgmt::PRNT_TO_NAME_KY;
			$qry .= "       ,lf.".MdLoc::$typeId." as ".APIMgmt::PRNT_FROM_TYPE_KY;
			$qry .= "       ,lt.".MdLoc::$typeId." as ".APIMgmt::PRNT_TO_TYPE_KY;
			
			$qry .= " FROM ";
			$qry .= "       ".MdLoc::$table." as lf," ;
			$qry .= "        ".MdLoc::$table." as lt," ;
			$qry .= "        ".TEqTrns::$table." as t " ;
			
			$qry .= " WHERE ";
			$qry .= "        lf.".MdLoc::$id." = t.".TEqTrns::$locIdFrom." " ;
			$qry .= " AND    lt.".MdLoc::$id." = t.".TEqTrns::$locIdTo." " ;
			$qry .= " AND    t.".TEqTrns::$equipmentId." > 1";
			$qry .= " AND    t.".TEqTrns::$equipmentId." = :eid";
			
			if ($df != null){
				$qry .= " AND    t.".TEqTrns::$time." > :df";
			}
			
			if ($dt!= null){
				$qry .= " AND    t.".TEqTrns::$time." < :dt";
			}

			$qry .= " ORDER BY  t.".TEqTrns::$time;

			$statement = $db->prepare($qry);

			$statement->bindParam(":eid", $eid, PDO::PARAM_INT);
			if ($df!= null){
				$statement->bindParam(":df", $df, PDO::PARAM_STR);
			}
			if ($dt!= null){
				$statement->bindParam(":dt", $dt, PDO::PARAM_STR);
			}
			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;


		} // getTEqTrnsWithLoc

		public static function getTLocTrnsForTrans($db, $lid, $df, $dt) {

			$qry =  " SELECT ";
			$qry .= "        t.".TLocTrns::$id;
			$qry .= "       ,t.".TLocTrns::$time;
			$qry .= "       ,t.".TLocTrns::$isLast;
			$qry .= "       ,lf.".MdLoc::$uuid." as ".APIMgmt::PRNT_FROM_KY;
			$qry .= "       ,lt.".MdLoc::$uuid." as ".APIMgmt::PRNT_TO_KY;
			$qry .= "       ,lf.".MdLoc::$name." as ".APIMgmt::PRNT_FROM_NAME_KY;
			$qry .= "       ,lt.".MdLoc::$name." as ".APIMgmt::PRNT_TO_NAME_KY;
			$qry .= "       ,lf.".MdLoc::$typeId." as ".APIMgmt::PRNT_FROM_TYPE_KY;
			$qry .= "       ,lt.".MdLoc::$typeId." as ".APIMgmt::PRNT_TO_TYPE_KY;
			
			$qry .= " FROM ";
			$qry .= "       ".MdLoc::$table." as lf," ;
			$qry .= "        ".MdLoc::$table." as lt," ;
			$qry .= "        ".TLocTrnsSub::$table." as ts," ;
			$qry .= "        ".TLocTrns::$table." as t " ;
			
			$qry .= " WHERE ";
			$qry .= "        lf.".MdLoc::$id." = ts.".TLocTrnsSub::$locIdFrom." " ;
			$qry .= " AND    lt.".MdLoc::$id." = ts.".TLocTrnsSub::$locIdTo." " ;
			$qry .= " AND    ts.".TLocTrnsSub::$trnsId." = t.".TLocTrns::$id." " ;
			$qry .= " AND    t.".TLocTrns::$locId." > 1";
			$qry .= " AND    t.".TLocTrns::$locId." = :lid";
			
			if ($df != null){
				$qry .= " AND    t.".TLocTrns::$time." > :df";
			}
			
			if ($dt != null){
				$qry .= " AND    t.".TLocTrns::$time." < :dt";
			}

			
			$qry .= " ORDER BY  t.".TLocTrns::$time;

			$statement = $db->prepare($qry);

			$statement->bindParam(":lid", $lid, PDO::PARAM_INT);
			if ($df != null){
				$statement->bindParam(":df", $df, PDO::PARAM_STR);
			}
			if ($dt != null){
				$statement->bindParam(":dt", $dt, PDO::PARAM_STR);
			}
			$statement->execute();

			$res = array();

			if($statement->rowCount() > 0){
				$res = $statement->fetchAll(PDO::FETCH_ASSOC);
			}

			return $res;


		} // getTEqTrnsWithLoc


		public static function canTakeTransport($db, $tid) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp," ;
			$qry .= "        ".MdLoc::$table." as prnt" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_PARKED." " ;
			$qry .= " AND    l.".MdLoc::$id." = :i";


			$statement = $db->prepare($qry);

			
			$statement->bindParam(":i", $tid, PDO::PARAM_INT);
			
			$statement->execute();

			return $statement->rowCount() > 0;


		} // canTakeTransport

		public static function canReturnTransport($db, $tid) {

			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			
			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp," ;
			$qry .= "        ".MdLoc::$table." as prnt" ;
			
			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    prnt.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId." " ;
			$qry .= " AND    prnt.".MdLoc::$id." = lp.".TLocProp::$locIdPrnt." " ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN." " ;
			$qry .= " AND    l.".MdLoc::$id." = :i";
			// Equipment can remain aboard when the transport is returned.

			$statement = $db->prepare($qry);
			
			$statement->bindParam(":i", $tid, PDO::PARAM_INT);
			
			$statement->execute();

			return $statement->rowCount() > 0;


		} // canReturnTransport

		public static function canTakeEquipment($db, $eid) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;

			$qry .= " FROM ";
			$qry .= "        ".MdEq::$table." as e," ;
			$qry .= "        ".TEqProp::$table." as ep," ;
			$qry .= "        ".MdLoc::$table." as l" ;

			$qry .= " WHERE ";

			$qry .= "        l.".MdLoc::$id." = ep.".TEqProp::$locId." " ;
			$qry .= " AND    e.".MdEq::$id." = ep.".TEqProp::$equipmentId." " ;
			$qry .= " AND    l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    e.".MdEq::$id." = :i";
			$qry .= " AND    e.".MdEq::$isActive." = 1";


			$statement = $db->prepare($qry);
			
			$statement->bindParam(":i", $eid, PDO::PARAM_INT);
			
			$statement->execute();

			return $statement->rowCount() > 0;


		} // canTakeEquipment

		public static function canLeaveEquipment($db, $eid, $uid, $lid) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;

			$qry .= " FROM ";
			$qry .= "        ".MdEq::$table." as e," ;
			$qry .= "        ".TEqProp::$table." as ep," ;
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp" ;

			$qry .= " WHERE ";

			$qry .= "        l.".MdLoc::$id." = ep.".TEqProp::$locId." " ;
			$qry .= " AND    e.".MdEq::$id." = ep.".TEqProp::$equipmentId." " ;
			$qry .= " AND    l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    l.".MdLoc::$id." = :lid" ;
			// Unloading follows current transport ownership, including equipment loaded by someone else.
			$qry .= " AND    lp.".TLocProp::$locId." = l.".MdLoc::$id ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN ;
			$qry .= " AND    lp.".TLocProp::$userId." = :uid" ;
			$qry .= " AND    e.".MdEq::$id." = :i";
			$qry .= " AND    e.".MdEq::$isActive." = 1";


			$statement = $db->prepare($qry);
			
			$statement->bindParam(":i", $eid, PDO::PARAM_INT);
			$statement->bindParam(":uid", $uid, PDO::PARAM_INT);
			$statement->bindParam(":lid", $lid, PDO::PARAM_INT);
			
			$statement->execute();

			return $statement->rowCount() > 0;


		} // canLeaveEquipment

		public static function canMoveTransport($db, $tid) {


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp " ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$id." = lp.".TLocProp::$locId ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    lp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN ;
			$qry .= " AND    l.".MdLoc::$id." = :i";

			$statement = $db->prepare($qry);
			
			$statement->bindParam(":i", $tid, PDO::PARAM_INT);
			
			$statement->execute();

			return $statement->rowCount() > 0;


		} // canMoveTransport

	
	}




	class UpdateData {

		public static function setRefreshToken($db, $uid, $token){

			$uid = intval($uid);

			$qry =  " UPDATE ";
			$qry .=  "  ".SyUser::$table. " SET ";

			$qry .=  " ".SyUser::$refreshToken." = :tkn " ;

			$qry .=  " WHERE " ;
			$qry .=  SyUser::$id. " = :id ";

			$statement = $db->prepare($qry);

			$statement->bindParam(":tkn", $token , PDO::PARAM_STR);

			$statement->bindValue(":id", $uid , PDO::PARAM_INT);
				
			
			
			$rowNo = $statement->execute();
			
			return $rowNo;



		}

		public static function updateEqByUUID($db, $uuid, $code, $name, $typeId, $isActive){

			$qry =  " UPDATE ";
			$qry .=  "  ".MdEq::$table. " SET ";

			$qry .=  " ".MdEq::$code." = :code, " ;
			$qry .=  " ".MdEq::$name." = :name, " ;
			$qry .=  " ".MdEq::$typeId." = :tid, " ;
			$qry .=  " ".MdEq::$isActive." = :ia " ;

			$qry .=  " WHERE " ;
			$qry .=  MdEq::$uuid. " LIKE :uuid ";

			$statement = $db->prepare($qry);

			$statement->bindValue(":code", $code , PDO::PARAM_STR);
			$statement->bindValue(":name", $name , PDO::PARAM_STR);
			$statement->bindValue(":tid", $typeId , PDO::PARAM_INT);
			$statement->bindValue(":ia", $isActive , PDO::PARAM_INT);

			$statement->bindValue(":uuid", $uuid , PDO::PARAM_STR);
			
			$rowNo = $statement->execute();
			
			return $rowNo;

		}

		public static function updateLocByUUID($db, $uuid, $code, $name, $desc, $isActive){

			$qry =  " UPDATE ";
			$qry .=  "  ".MdLoc::$table. " SET ";

			$qry .=  " ".MdLoc::$code." = :code, " ;
			$qry .=  " ".MdLoc::$name." = :name, " ;
			$qry .=  " ".MdLoc::$desc." = :desc, " ;
			$qry .=  " ".MdLoc::$isActive." = :ia " ;

			$qry .=  " WHERE " ;
			$qry .=  MdLoc::$uuid. " LIKE :uuid ";

			$statement = $db->prepare($qry);

			$statement->bindValue(":code", $code , PDO::PARAM_STR);
			$statement->bindValue(":name", $name , PDO::PARAM_STR);
			$statement->bindValue(":desc", $desc , PDO::PARAM_STR);
			$statement->bindValue(":ia", $isActive , PDO::PARAM_INT);

			$statement->bindValue(":uuid", $uuid , PDO::PARAM_STR);
			
			$rowNo = $statement->execute();
			
			return $rowNo;

		}

	}

	class Transaction {

		public static function createEquipment($db, $data, $lid, $tid, $uid){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".MdEq::$table. " ( ";
			
				$qry .=  "  ".MdEq::$uuid;
				$qry .=  " ,".MdEq::$code;
				$qry .=  " ,".MdEq::$name;
				$qry .=  " ,".MdEq::$isActive;
				$qry .=  " ,".MdEq::$typeId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :uuid ";
				$qry .=  " ,:code ";
				$qry .=  " ,:name ";
				$qry .=  " ,:ia ";
				$qry .=  " ,:type ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":uuid", $data[APIMgmt::UUID] , PDO::PARAM_STR);
				$statement->bindParam(":code", $data[APIMgmt::CODE] , PDO::PARAM_STR);
				$statement->bindParam(":name", $data[APIMgmt::NAME] , PDO::PARAM_STR);
				$statement->bindParam(":ia", $data[APIMgmt::ISACTIVE] , PDO::PARAM_INT);
				$statement->bindParam(":type", $tid , PDO::PARAM_INT);
				$statement->execute();

				$eid = $db->lastInsertId();
				$statement->closeCursor();


				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TEqProp::$table. " ( ";
			
				$qry .=  "  ".TEqProp::$locId;
				$qry .=  " ,".TEqProp::$userId;
				$qry .=  " ,".TEqProp::$equipmentId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :lid ";
				$qry .=  " ,:uid ";
				$qry .=  " ,:eid ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->bindParam(":eid", $eid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$db->commit();

				return true;

			} catch (PDOException $e) {
				$db->rollBack();
				throw $e;
			}


		}

		public static function createLocation($db, $data, $prnt_id, $tid, $sid, $uid){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".MdLoc::$table. " ( ";
			
				$qry .=  "  ".MdLoc::$uuid;
				$qry .=  " ,".MdLoc::$code;
				$qry .=  " ,".MdLoc::$name;
				$qry .=  " ,".MdLoc::$desc;
				$qry .=  " ,".MdLoc::$isActive;
				$qry .=  " ,".MdLoc::$typeId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :uuid ";
				$qry .=  " ,:code ";
				$qry .=  " ,:name ";
				$qry .=  " ,:desc ";
				$qry .=  " ,:ia ";
				$qry .=  " ,:type ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":uuid", $data[APIMgmt::UUID] , PDO::PARAM_STR);
				$statement->bindParam(":code", $data[APIMgmt::CODE] , PDO::PARAM_STR);
				$statement->bindParam(":name", $data[APIMgmt::NAME] , PDO::PARAM_STR);
				$statement->bindParam(":desc", $data[APIMgmt::DESC] , PDO::PARAM_STR);
				$statement->bindParam(":ia", $data[APIMgmt::ISACTIVE] , PDO::PARAM_INT);
				$statement->bindParam(":type", $tid , PDO::PARAM_INT);
				$statement->execute();

				$lid = $db->lastInsertId();
				$statement->closeCursor();


				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TLocProp::$table. " ( ";
			
				$qry .=  "  ".TLocProp::$locId;
				$qry .=  " ,".TLocProp::$userId;
				$qry .=  " ,".TLocProp::$locStatId;
				$qry .=  " ,".TLocProp::$locIdPrnt;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :lid ";
				$qry .=  " ,:uid ";
				$qry .=  " ,:sid ";
				$qry .=  " ,:parent ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->bindParam(":sid", $sid , PDO::PARAM_INT);
				$statement->bindParam(":parent", $prnt_id , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$db->commit();

				return true;

			} catch (PDOException $e) {
				$db->rollBack();
				throw $e;
			}


		}

		public static function locationStatusChange($db, $lid, $uid, $stat_f, $stat_t){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TLocTrns::$table. " SET ";

				$qry .=  " ".TLocTrns::$isLast." = 0 " ;

				$qry .=  " WHERE " ;
				$qry .=  TLocTrns::$locId. " = :lid ";
				$qry .=  " AND ";
				$qry .=  TLocTrns::$isLast. " = 1; ";

				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();



				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TLocTrns::$table. " ( ";
			
				$qry .=  "  ".TLocTrns::$locId;
				$qry .=  " ,".TLocTrns::$locStatIdFrom;
				$qry .=  " ,".TLocTrns::$locStatIdTo;
				$qry .=  " ,".TLocTrns::$userId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :lid ";
				$qry .=  " ,:sf ";
				$qry .=  " ,:st ";
				$qry .=  " ,:uid ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":sf", $stat_f , PDO::PARAM_INT);
				$statement->bindParam(":st", $stat_t , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();


				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TLocProp::$table. " SET ";
	
				$qry .=  " ".TLocProp::$userId." = :uid, " ;
				$qry .=  " ".TLocProp::$time." = now(), " ;
				$qry .=  " ".TLocProp::$locStatId." = :st " ;
	
				$qry .=  " WHERE " ;
				$qry .=  TLocProp::$locId. " = :lid; ";		
			

				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":st", $stat_t , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$db->commit();

				return true;

			} catch (PDOException $e) {
				$db->rollBack();
				throw $e;
			}


		}


		public static function equipmentLocationChange($db, $eid, $uid, $lfid, $ltid){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TEqTrns::$table. " SET ";

				$qry .=  " ".TEqTrns::$isLast." = 0 " ;

				$qry .=  " WHERE " ;
				$qry .=  TEqTrns::$equipmentId. " = :eid ";
				$qry .=  " AND ";
				$qry .=  TEqTrns::$isLast. " = 1; ";

				$statement = $db->prepare($qry);
				$statement->bindParam(":eid", $eid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();



				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TEqTrns::$table. " ( ";
			
				$qry .=  "  ".TEqTrns::$equipmentId;
				$qry .=  " ,".TEqTrns::$locIdFrom;
				$qry .=  " ,".TEqTrns::$locIdTo;
				$qry .=  " ,".TEqTrns::$userId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :eid ";
				$qry .=  " ,:lfid ";
				$qry .=  " ,:ltid ";
				$qry .=  " ,:uid ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":eid", $eid , PDO::PARAM_INT);
				$statement->bindParam(":lfid", $lfid , PDO::PARAM_INT);
				$statement->bindParam(":ltid", $ltid , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();


				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TEqProp::$table. " SET ";
	
				$qry .=  " ".TEqProp::$userId." = :uid, " ;
				$qry .=  " ".TEqProp::$time." = now(), " ;
				$qry .=  " ".TEqProp::$locId." = :ltid " ;
	
				$qry .=  " WHERE " ;
				$qry .=  TEqProp::$equipmentId. " = :eid; ";		
			

				$statement = $db->prepare($qry);
				$statement->bindParam(":eid", $eid , PDO::PARAM_INT);
				$statement->bindParam(":ltid", $ltid , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$db->commit();

				return true;

			} catch (PDOException $e) {
				$db->rollBack();
				throw $e;
			}


		}

		public static function transportLocationChange($db, $lid, $uid, $lfid, $ltid, $status){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TLocTrns::$table. " SET ";

				$qry .=  " ".TLocTrns::$isLast." = 0 " ;

				$qry .=  " WHERE " ;
				$qry .=  TLocTrns::$locId. " = :lid ";
				$qry .=  " AND ";
				$qry .=  TLocTrns::$isLast. " = 1; ";

				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();



				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TLocTrns::$table. " ( ";
			
				$qry .=  "  ".TLocTrns::$locId;
				$qry .=  " ,".TLocTrns::$locStatIdFrom;
				$qry .=  " ,".TLocTrns::$locStatIdTo;
				$qry .=  " ,".TLocTrns::$userId;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :lid ";
				$qry .=  " ,:s ";
				$qry .=  " ,:s ";
				$qry .=  " ,:uid ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":s", $status , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$insertedId = $db->lastInsertId();

				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".TLocTrnsSub::$table. " ( ";
			
				$qry .=  "  ".TLocTrnsSub::$trnsId;
				$qry .=  " ,".TLocTrnsSub::$locIdFrom;
				$qry .=  " ,".TLocTrnsSub::$locIdTo;
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :tid ";
				$qry .=  " ,:lfid ";
				$qry .=  " ,:ltid ";
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":tid", $insertedId , PDO::PARAM_INT);
				$statement->bindParam(":lfid", $lfid , PDO::PARAM_INT);
				$statement->bindParam(":ltid", $ltid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();


				$qry = "";
				$qry .=  " UPDATE ";
				$qry .=  "  ".TLocProp::$table. " SET ";
	
				$qry .=  " ".TLocProp::$userId." = :uid, " ;
				$qry .=  " ".TLocProp::$time." = now(), " ;
				$qry .=  " ".TLocProp::$locIdPrnt." = :ltid " ;
	
				$qry .=  " WHERE " ;
				$qry .=  TLocProp::$locId. " = :lid; ";		
			

				$statement = $db->prepare($qry);
				$statement->bindParam(":lid", $lid , PDO::PARAM_INT);
				$statement->bindParam(":ltid", $ltid , PDO::PARAM_INT);
				$statement->bindParam(":uid", $uid , PDO::PARAM_INT);
				$statement->execute();
				$statement->closeCursor();

				$db->commit();

				return true;

			} catch (PDOException $e) {
				$db->rollBack();
				throw $e;
			}


		}
	}


	class CreateData {


		

	}










/*
 *   DB object mapping BELOW
 */
	
	class SyUser {

		public static $table     = 'sy_user';
	
        public static $id        = 'SY_USER_ID';
        public static $name      = 'SY_USER_NAME';
        public static $surr     = 'SY_USER_SURRNAME';
        public static $pwd    = 'SY_USER_PWD';		
        public static $email  = 'SY_USER_EMAIL';
        public static $isActive  = 'SY_USER_ISACTIVE';
        public static $aclId  = 'SY_USER_ACL_ID';
        public static $locationId  = 'MD_LOC_ID';
        public static $refreshToken  = 'SY_USER_REFRESH_TKN';

		
		public static function getSyUser($c, $i) {

			$qry = "SELECT * " ;
			$qry .= " FROM " . self::$table ;
			$qry .= " WHERE ".self::$id . " = :id" ;

			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();

			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $res;

		}		
		
		
	} // SyUser


	
	class SyUserAcl {

		public static $table       = 'sy_user_acl';
	
		public static $id          = 'SY_USER_ACL_ID';
		public static $code        = 'SY_USER_ACL_CODE';

		const ADMIN = 1;
		const OWNER = 2;
		const MANAGER = 3;
		const OPERATOR = 4;

		public static function hasPrivilege($user, $privilege){
			return $user[SyUser::$aclId] <= $privilege;
		}

	
		public static function getSyUserAcl($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}




	class MdLoc {

		public static $table       = 'md_loc';
	
		public static $id          = 'MD_LOC_ID';
		public static $uuid        = 'MD_LOC_UUID';
		public static $code        = 'MD_LOC_CODE';
		public static $name        = 'MD_LOC_NAME'; 
		public static $desc        = 'MD_LOC_DESC';
		public static $isActive    = 'MD_LOC_ISACTIVE';
		public static $typeId      = 'MD_LOC_TYPE_ID';
	
		public static function getMdLoc($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getMdLocByUUID($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$uuid . " LIKE :uuid";

			$stmt = $c->prepare($qry);
			$stmt->bindParam(":uuid", $i, PDO::PARAM_STR);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		public static function isMdLocTransport($c,$i){
			$qry =  " SELECT ";
			$qry .= "        l.".self::$id;

			$qry .= " FROM ";
			$qry .= "        ".self::$table." as l" ;

			$qry .= " WHERE ";
			$qry .= "        l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::TRANSPORT]) ;
			$qry .= " AND    l.".MdLoc::$id." = :i";


			$statement = $c->prepare($qry);
			$statement->bindParam(":i", $i, PDO::PARAM_INT);
			$statement->execute();

			return $statement->rowCount() > 0;
		}

	}

	class MdLocType {

		const TRANSPORT = 1;
		const CONSTRUCTION_SITE = 2;
		const WAREHOUSE = 3;

		public static $table       = 'md_loc_type';
	
		public static $id          = 'MD_LOC_TYPE_ID';
		public static $name        = 'MD_LOC_TYPE_NAME';
		public static $desc        = 'MD_LOC_TYPE_DESC';
	
		public static function getMdLocType($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		public static function getTypesForSQL($types){
			return '(' . implode(',', $types) . ')';
		}

	}

	class MdLocStat {

		const T_PARKED = 2;
		const T_TAKEN = 3;
		const L_OPEN = 4;
		const L_CLOSED = 5;


		public static $table       = 'md_loc_stat';
	
		public static $id          = 'MD_LOC_STAT_ID';
		public static $code        = 'MD_LOC_STAT_CODE';
		public static $name        = 'MD_LOC_STAT_NAME';
		public static $typeId        = 'MD_LOC_TYPE_ID';
	
		public static function getMdLocStat($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}


	class MdCntrctr {

		public static $table       = 'md_cntrctr';
	
		public static $id          = 'MD_CNTRCTR_ID';
		public static $code        = 'MD_CNTRCTR_CODE';
		public static $name        = 'MD_CNTRCTR_NAME';
		public static $vat         = 'MD_CNTRCTR_VAT';
		public static $isActive    = 'MD_CNTRCTR_ISACTIVE';
	
		public static function getMdCntrctr($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}

	class MdEq {

		public static $table       = 'md_eq';
	
		public static $id          = 'MD_EQ_ID';
		public static $uuid        = 'MD_EQ_UUID';
		public static $code        = 'MD_EQ_CODE';
		public static $name        = 'MD_EQ_NAME';
		public static $isActive    = 'MD_EQ_ISACTIVE';
		public static $typeId      = 'MD_EQ_TYPE_ID';
	
		public static function getMdEq($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getMdEqByUUID($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$uuid . " LIKE :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_STR);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getActive($c) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$isActive . " = 1 AND ".self::$id." > 1";
			
			$stmt = $c->prepare($qry);
			$stmt->execute();
	
			return  $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	
		public static function getAll($c) {
			$qry = "SELECT * FROM " . self::$table . " WHERE ".self::$id." > 1";
			
			$stmt = $c->prepare($qry);
			$stmt->execute();
	
			return  $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	
	}

	class MdEqType {

		public static $table       = 'md_eq_type';
	
		public static $id          = 'MD_EQ_TYPE_ID';
		public static $code        = 'MD_EQ_TYPE_CODE';
		public static $name        = 'MD_EQ_TYPE_NAME';
	
		public static function getMdEqType($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getAll($c) {
			$qry = "SELECT * FROM " . self::$table;
			
			$stmt = $c->prepare($qry);
			$stmt->execute();
	
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}


	class TEqProp {
		public static $table = 't_eq_prop';
		
		public static $id = 'T_EQ_PROP_ID';
		public static $locId = 'MD_LOC_ID';
		public static $time = 'T_EQ_PROP_TIME';
		public static $userId = 'SY_USER_ID';
		public static $contractorId = 'MD_CNTRCTR_ID';
		public static $equipmentId = 'MD_EQ_ID';
	
		public static function getTEqProp($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getTEqPropByEqId($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$equipmentId . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();

			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $res;
		}
	}

	class TEqTrns {
		public static $table = 't_eq_trns';
		
		public static $id = 'T_EQ_TRNS_ID';
		public static $time = 'T_EQ_TRNS_TIME';
		public static $isLast = 'T_EQ_TRNS_ISLAST';
		public static $equipmentId = 'MD_EQ_ID';
		public static $locIdFrom = 'MD_LOC_ID_FROM';
		public static $locIdTo = 'MD_LOC_ID_TO';
		public static $userId = 'SY_USER_ID';
		public static $contractorId = 'MD_CNTRCTR_ID';
	
		public static function getTEqTrns($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}
	
	class TLocProp {
		public static $table = 't_loc_prop';
		
		public static $id = 'T_LOC_PROP_ID';
		public static $time = 'T_LOC_PROP_TIME';
		public static $locStatId = 'MD_LOC_STAT_ID';
		public static $userId = 'SY_USER_ID';
		public static $locId = 'MD_LOC_ID';
		public static $locIdPrnt = 'MD_LOC_ID_PRNT';
	
		public static function getTLocProp($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		public static function getTLocPropByLocId($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$locId . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}
	
	class TLocTrns {
		public static $table = 't_loc_trns';
		
		public static $id = 'T_LOC_TRNS_ID';
		public static $locId = 'MD_LOC_ID';
		public static $time = 'T_LOC_TRNS_TIME';
		public static $isLast = 'T_LOC_TRNS_ISLAST';
		public static $locStatIdFrom = 'MD_LOC_STAT_ID_FROM';
		public static $locStatIdTo = 'MD_LOC_STAT_ID_TO';
		public static $userId = 'SY_USER_ID';
	
		public static function getTLocTrns($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}
	
	class TLocTrnsSub {
		public static $table = 't_loc_trns_sub';
		
		public static $id = 'T_LOC_TRNS_SUB_ID';
		public static $trnsId = 'T_LOC_TRNS_ID';
		public static $locIdFrom = 'MD_LOC_ID_FROM';
		public static $locIdTo = 'MD_LOC_ID_TO';
	
		public static function getTLocTrnsSub($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}



?>