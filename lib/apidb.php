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
			$qry .= "       ,ep.".TEqProp::$serviceStatId;
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


        public static function getTeleportDestinations($db) {
            $query = 'SELECT l.*, (SELECT COUNT(*) FROM ' . TLocProp::$table . ' p WHERE p.' . TLocProp::$locId
                . ' = l.' . MdLoc::$id . ') AS property_count FROM ' . MdLoc::$table . ' l'
                . ' WHERE l.' . MdLoc::$isActive . ' = 1 AND l.' . MdLoc::$id . ' > 1'
                . ' ORDER BY l.' . MdLoc::$typeId . ', l.' . MdLoc::$name . ', l.' . MdLoc::$id;
            $statement = $db->prepare($query);
            if (!$statement || !$statement->execute()) { return null; }

            $destinations = array();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (MdLoc::isTeleportDestination($row, (int) $row['property_count'])) { $destinations[] = $row; }
            }
            return $destinations;
        }

        public static function getServiceLocations($db) {
            $rows = self::readRows($db, 'SELECT l.MD_LOC_ID AS id, l.MD_LOC_UUID AS uuid,
                l.MD_LOC_CODE AS code, l.MD_LOC_NAME AS name, l.MD_LOC_DESC AS `desc`, l.MD_LOC_ISACTIVE AS isActive,
                lp.MD_LOC_STAT_ID AS statusId FROM md_loc l JOIN t_loc_prop lp ON lp.MD_LOC_ID = l.MD_LOC_ID
                WHERE l.MD_LOC_TYPE_ID = ? ORDER BY l.MD_LOC_NAME, l.MD_LOC_ID', array(MdLocType::SERVICE));
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['isActive'] = (int) $row['isActive'];
                $row['statusId'] = (int) $row['statusId'];
            }
            return $rows;
        }

        public static function getServiceTypeOptions($db, $locationId) {
            $rows = self::readRows($db, 'SELECT s.MD_EQ_TYPE_SECONDARY_ID AS id,
                s.MD_EQ_TYPE_SECONDARY_NAME AS name, p.MD_EQ_TYPE_PRIMARY_ID AS primaryId,
                p.MD_EQ_TYPE_PRIMARY_NAME AS primaryName,
                EXISTS (SELECT 1 FROM t_loc_service_eq_types t WHERE t.MD_LOC_ID = ?
                    AND t.MD_EQ_TYPE_SECONDARY_ID = s.MD_EQ_TYPE_SECONDARY_ID) AS selected
                FROM md_eq_type_secondary s JOIN md_eq_type_primary p ON p.MD_EQ_TYPE_PRIMARY_ID = s.MD_EQ_TYPE_PRIMARY_ID
                ORDER BY p.MD_EQ_TYPE_PRIMARY_NAME, p.MD_EQ_TYPE_PRIMARY_ID,
                    s.MD_EQ_TYPE_SECONDARY_NAME, s.MD_EQ_TYPE_SECONDARY_ID', array($locationId));
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['primaryId'] = (int) $row['primaryId'];
                $row['selected'] = (bool) $row['selected'];
            }
            return $rows;
        }

        public static function serviceDropOffAllowed($location, $property) {
            return $property && (int) $location[MdLoc::$isActive] === 1
                && (int) $property[TLocProp::$locStatId] === MdLocStat::SERVICE_OPEN;
        }

        public static function serviceTransportAllowed($transport, $property, $userId, $serviceId) {
            return $transport && $property && (int) $transport[MdLoc::$typeId] === MdLocType::TRANSPORT
                && (int) $transport[MdLoc::$isActive] === 1
                && (int) $property[TLocProp::$locStatId] === MdLocStat::T_TAKEN
                && (int) $property[TLocProp::$userId] === $userId
                && (int) $property[TLocProp::$locIdPrnt] === $serviceId;
        }

        public static function serviceTransportOptions($db, $uuid, $userId, $pickup) {
            $transport = MdLoc::getMdLocByUUID($db, $uuid);
            if (!$transport || $transport[MdLoc::$uuid] !== $uuid) { return null; }
            $property = TLocProp::getTLocPropByLocId($db, $transport[MdLoc::$id]);
            $serviceId = (int) ($property[TLocProp::$locIdPrnt] ?? 0);
            $service = $serviceId > 1 ? MdLoc::getMdLoc($db, $serviceId) : false;
            if (!$service || (int) $service[MdLoc::$typeId] !== MdLocType::SERVICE
                || !self::serviceTransportAllowed($transport, $property, $userId, $serviceId)) { return null; }
            $serviceProperty = TLocProp::getTLocPropByLocId($db, $serviceId);
            if (!$serviceProperty) { return null; }
            $open = self::serviceDropOffAllowed($service, $serviceProperty);
            $rows = array();
            if ($open || $pickup) {
                $sql = 'SELECT e.MD_EQ_ID AS id, e.MD_EQ_UUID AS uuid, e.MD_EQ_CODE AS code, e.MD_EQ_NAME AS name,
                    ep.MD_EQ_SERVICE_STAT_ID AS serviceStatusId FROM t_eq_prop ep JOIN md_eq e ON e.MD_EQ_ID = ep.MD_EQ_ID
                    WHERE ep.MD_LOC_ID = ? AND e.MD_EQ_ID > 1 AND e.MD_EQ_ISACTIVE = 1
                    AND NOT EXISTS (SELECT 1 FROM t_eq_prop other WHERE other.MD_EQ_ID = e.MD_EQ_ID
                        AND other.T_EQ_PROP_ID <> ep.T_EQ_PROP_ID)';
                $params = array($pickup ? $serviceId : $transport[MdLoc::$id]);
                if ($pickup) {
                    $sql .= ' AND ep.MD_EQ_SERVICE_STAT_ID = ?';
                    $params[] = MdEqServiceStat::IN_SERVICE;
                } else {
                    $sql .= ' AND ep.MD_EQ_SERVICE_STAT_ID IN (?, ?)
                        AND EXISTS (SELECT 1 FROM t_loc_service_eq_types t WHERE t.MD_LOC_ID = ?
                            AND t.MD_EQ_TYPE_SECONDARY_ID = e.MD_EQ_TYPE_SECONDARY_ID)';
                    array_push($params, MdEqServiceStat::NEEDS_SERVICE, MdEqServiceStat::BROKEN, $serviceId);
                }
                $rows = self::readRows($db, $sql . ' ORDER BY e.MD_EQ_NAME, e.MD_EQ_ID', $params);
                foreach ($rows as &$row) {
                    $row['id'] = (int) $row['id'];
                    $row['serviceStatusId'] = (int) $row['serviceStatusId'];
                }
            }
            return array('id' => $serviceId, 'uuid' => $service[MdLoc::$uuid], 'name' => $service[MdLoc::$name],
                'transportUuid' => $uuid, 'pickupOnly' => !$open, 'equipment' => $rows);
        }

        public static function serviceHistory($db, $equipmentId) {
            $rows = self::readRows($db, 'SELECT T_EQ_SERVICE_ID AS id, T_EQ_SERVICE_WARRANTY_DATE AS warrantyDate,
                T_EQ_SERVICE_TIME AS time, T_EQ_SERVICE_ISLAST AS isLast, T_EQ_SERVICE_ISBROKEN AS isBroken
                FROM t_eq_service WHERE MD_EQ_ID = ? ORDER BY T_EQ_SERVICE_ID DESC', array($equipmentId));
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['isLast'] = (int) $row['isLast'];
                $row['isBroken'] = (int) $row['isBroken'];
            }
            return $rows;
        }

        private static function serviceEquipmentExistsSql($locationSql) {
            return 'EXISTS (SELECT 1 FROM t_eq_prop ep JOIN md_eq e ON e.MD_EQ_ID = ep.MD_EQ_ID
                WHERE ep.MD_LOC_ID = ' . $locationSql . ' AND ep.MD_EQ_SERVICE_STAT_ID = ' . MdEqServiceStat::IN_SERVICE . '
                AND e.MD_EQ_ID > 1 AND e.MD_EQ_ISACTIVE = 1)';
        }

        public static function hasServiceEquipment($db, $serviceId) {
            $statement = $db->prepare('SELECT ' . self::serviceEquipmentExistsSql('?'));
            if (!$statement || !$statement->execute(array($serviceId))) {
                throw new \RuntimeException('Service equipment lookup failed');
            }
            return (bool) $statement->fetchColumn();
        }

        public static function isChecklistWarehouse($location) {
            return $location && (int) $location[MdLoc::$id] > 1
                && (int) $location[MdLoc::$typeId] === MdLocType::WAREHOUSE
                && (int) $location[MdLoc::$isActive] === 1;
        }

        public static function getChecklistStock($db, $warehouseId) {
            $sql = <<<'SQL'
                SELECT e.MD_EQ_ID AS id, e.MD_EQ_NAME AS name, e.MD_EQ_CODE AS code,
                       p.MD_EQ_TYPE_PRIMARY_ID AS typeId, p.MD_EQ_TYPE_PRIMARY_NAME AS typeName,
                       s.MD_EQ_TYPE_SECONDARY_ID AS secondaryTypeId, s.MD_EQ_TYPE_SECONDARY_NAME AS secondaryTypeName
                FROM t_eq_prop ep
                JOIN md_eq e ON e.MD_EQ_ID = ep.MD_EQ_ID
                JOIN md_eq_type_primary p ON p.MD_EQ_TYPE_PRIMARY_ID = e.MD_EQ_TYPE_PRIMARY_ID
                JOIN md_eq_type_secondary s ON s.MD_EQ_TYPE_SECONDARY_ID = e.MD_EQ_TYPE_SECONDARY_ID
                    AND s.MD_EQ_TYPE_PRIMARY_ID = p.MD_EQ_TYPE_PRIMARY_ID
                WHERE ep.MD_LOC_ID = ? AND e.MD_EQ_ISACTIVE = 1 AND e.MD_EQ_ID > 1
                    AND ep.MD_EQ_SERVICE_STAT_ID <> ?
                    AND NOT EXISTS (
                        SELECT 1 FROM t_eq_prop other
                        WHERE other.MD_EQ_ID = ep.MD_EQ_ID AND other.T_EQ_PROP_ID <> ep.T_EQ_PROP_ID
                    )
                ORDER BY e.MD_EQ_NAME, e.MD_EQ_CODE, e.MD_EQ_ID
                SQL;
            $rows = self::readRows($db, $sql, array($warehouseId, MdEqServiceStat::IN_SERVICE));
            foreach ($rows as &$row) {
                foreach (array('id', 'typeId', 'secondaryTypeId') as $field) {
                    $row[$field] = (int) $row[$field];
                }
            }
            return $rows;
        }

        private static function readRows($db, $sql, $params) {
            $statement = $db->prepare($sql);
            if (!$statement || !$statement->execute($params)) {
                throw new \RuntimeException('Database read failed');
            }
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        public static function getAssignedChecklists($db, $userId, $warehouseId = null, $openOnly = false, $id = null) {
            $sql = <<<'SQL'
                SELECT c.T_EQ_CHECKLIST_ID AS id, c.T_EQ_CHECKLIST_TIME AS createdAt,
                       c.T_EQ_CHECKLIST_ISCLOSED AS isClosed, c.MD_LOC_ID AS warehouseId,
                       l.MD_LOC_NAME AS warehouseName,
                       TRIM(CONCAT_WS(' ', u.SY_USER_NAME, u.SY_USER_SURRNAME)) AS creatorName,
                       c.SY_USER_ID_AUTHOR AS creatorId
                FROM t_eq_checklist c
                LEFT JOIN md_loc l ON l.MD_LOC_ID = c.MD_LOC_ID
                LEFT JOIN sy_user u ON u.SY_USER_ID = c.SY_USER_ID_AUTHOR
                WHERE c.SY_USER_ID_ASSIGNEE = ?
                SQL;
            $params = array($userId);
            if ($warehouseId !== null) {
                $sql .= ' AND c.MD_LOC_ID = ?';
                $params[] = $warehouseId;
            }
            if ($openOnly) { $sql .= ' AND c.T_EQ_CHECKLIST_ISCLOSED = 0'; }
            if ($id !== null) {
                $sql .= ' AND c.T_EQ_CHECKLIST_ID = ?';
                $params[] = $id;
            }
            $sql .= ' ORDER BY c.T_EQ_CHECKLIST_TIME DESC, c.T_EQ_CHECKLIST_ID DESC';
            // A detail read holds the header until its credit rows have been read.
            if ($id !== null) { $sql .= ' LOCK IN SHARE MODE'; }
            $rows = self::readRows($db, $sql, $params);
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['warehouseId'] = (int) $row['warehouseId'];
                $row['isClosed'] = (int) $row['isClosed'];
                $row['warehouseName'] = $row['warehouseName'] ?? (string) $row['warehouseId'];
                $row['creatorName'] = $row['creatorName'] ?: (string) $row['creatorId'];
                unset($row['creatorId']);
            }
            return $rows;
        }

        public static function getChecklistLines($db, $checklistId, $includeCredits = false) {
            $sql = <<<'SQL'
                SELECT i.T_EQ_CHECKLIST_ITEM_ID AS id, i.T_EQ_CHECKLIST_ITEM_AMOUNT AS quantity,
                       i.MD_EQ_TYPE_PRIMARY_ID AS typeId, p.MD_EQ_TYPE_PRIMARY_NAME AS typeName,
                       i.MD_EQ_TYPE_SECONDARY_ID AS secondaryTypeId, s.MD_EQ_TYPE_SECONDARY_NAME AS secondaryTypeName,
                       i.MD_EQ_ID AS equipmentId, e.MD_EQ_NAME AS equipmentName, e.MD_EQ_CODE AS equipmentCode
                FROM t_eq_checklist_item i
                LEFT JOIN md_eq_type_primary p ON p.MD_EQ_TYPE_PRIMARY_ID = i.MD_EQ_TYPE_PRIMARY_ID
                LEFT JOIN md_eq_type_secondary s ON s.MD_EQ_TYPE_SECONDARY_ID = i.MD_EQ_TYPE_SECONDARY_ID
                LEFT JOIN md_eq e ON e.MD_EQ_ID = i.MD_EQ_ID
                WHERE i.T_EQ_CHECKLIST_ID = ?
                ORDER BY i.T_EQ_CHECKLIST_ITEM_ID
                SQL;
            $rows = self::readRows($db, $sql, array($checklistId));
            $credits = array();
            if ($includeCredits) {
                $sql = <<<'SQL'
                    SELECT m.T_EQ_CHECKLIST_ITEM_ID AS lineId, m.MD_EQ_ID AS id,
                           m.T_EQ_TRNS_ID AS movementId, e.MD_EQ_NAME AS name, e.MD_EQ_CODE AS code
                    FROM t_eq_checklist_item i
                    JOIN t_eq_checklist_item_trns m ON m.T_EQ_CHECKLIST_ITEM_ID = i.T_EQ_CHECKLIST_ITEM_ID
                    JOIN t_eq_trns h ON h.T_EQ_TRNS_ID = m.T_EQ_TRNS_ID AND h.MD_EQ_ID = m.MD_EQ_ID
                    LEFT JOIN md_eq e ON e.MD_EQ_ID = m.MD_EQ_ID
                    WHERE i.T_EQ_CHECKLIST_ID = ?
                    ORDER BY m.T_EQ_CHECKLIST_ITEM_TRNS_ID
                    SQL;
                foreach (self::readRows($db, $sql, array($checklistId)) as $credit) {
                    $id = (int) $credit['id'];
                    $credits[$credit['lineId']][] = array('id' => $id, 'movementId' => (int) $credit['movementId'],
                        'name' => $credit['name'] ?? (string) $id, 'code' => $credit['code']);
                }
            }
            foreach ($rows as &$line) {
                foreach (array('id', 'quantity', 'typeId', 'secondaryTypeId', 'equipmentId') as $field) {
                    if ($line[$field] !== null) { $line[$field] = (int) $line[$field]; }
                }
                $line['typeName'] = $line['typeName'] ?? (string) $line['typeId'];
                if ($line['secondaryTypeId'] !== null) {
                    $line['secondaryTypeName'] = $line['secondaryTypeName'] ?? (string) $line['secondaryTypeId'];
                }
                if ($line['equipmentId'] !== null) {
                    $line['equipmentName'] = $line['equipmentName'] ?? (string) $line['equipmentId'];
                }
                $line['credited'] = $credits[$line['id']] ?? array();
                $line['remaining'] = $line['quantity'] - count($line['credited']);
            }
            return $rows;
        }

        public static function getAssignedChecklist($db, $userId, $id) {
            if (!$db->beginTransaction()) { throw new \RuntimeException('Database read failed'); }
            try {
                $header = self::getAssignedChecklists($db, $userId, null, false, $id)[0] ?? null;
                if ($header !== null) {
                    $header['lines'] = self::getChecklistLines($db, $id, $header['isClosed'] === 1);
                }
                if (!$db->commit()) { throw new \RuntimeException('Database read failed'); }
                return $header;
            } catch (\Throwable $error) {
                if ($db->inTransaction()) { $db->rollBack(); }
                throw $error;
            }
        }

		public static function getAvailibleEquipmentOnLocation($db, $lid) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;
			$qry .= "       ,e.".MdEq::$name;
			$qry .= "       ,e.".MdEq::$code;
			$qry .= "       ,e.".MdEq::$uuid;
			$qry .= "       ,e.".MdEq::$isActive;
			$qry .= "       ,e.".MdEq::$primaryTypeId;
			$qry .= "       ,e.".MdEq::$secondaryTypeId;
			$qry .= "       ,ep.".TEqProp::$serviceStatId;

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
            if (!is_int($offset) || !is_int($rowno) || $offset < 0
                || $offset > (APIMgmt::PAGINATION_MAX_PAGE - 1) * APIMgmt::PAGINATION_MAX_RPP
                || $rowno < 1 || $rowno > APIMgmt::PAGINATION_MAX_RPP) { return null; }


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

			$qry .= " LIMIT :offset, :rowno";

			$statement = $db->prepare($qry);
            $statement->bindValue(":offset", $offset, PDO::PARAM_INT);
            $statement->bindValue(":rowno", $rowno, PDO::PARAM_INT);

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

		public static function getAvailibleLocationList($db, $ordr, $f, $offset, $rowno, $includeService = false) {
            if (!is_int($offset) || !is_int($rowno) || $offset < 0
                || $offset > (APIMgmt::PAGINATION_MAX_PAGE - 1) * APIMgmt::PAGINATION_MAX_RPP
                || $rowno < 1 || $rowno > APIMgmt::PAGINATION_MAX_RPP) { return null; }


			$qry =  " SELECT ";
			$qry .= "        l.".MdLoc::$id;
			$qry .= "       ,l.".MdLoc::$name;
			$qry .= "       ,l.".MdLoc::$code;
			$qry .= "       ,l.".MdLoc::$uuid;
			$qry .= "       ,l.".MdLoc::$desc;
			$qry .= "       ,l.".MdLoc::$isActive;

            if ($includeService) {
                $qry .= ', l.MD_LOC_TYPE_ID, CASE WHEN l.MD_LOC_TYPE_ID = ' . MdLocType::SERVICE
                    . ' AND (l.MD_LOC_ISACTIVE <> 1 OR lp.MD_LOC_STAT_ID <> ' . MdLocStat::SERVICE_OPEN
                    . ') THEN 1 ELSE 0 END AS pickupOnly';
            }

			$qry .= " FROM ";
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".TLocProp::$table." as lp " ;

			$regular = 'l.MD_LOC_TYPE_ID IN ' . MdLocType::getTypesForSQL([MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE])
                . ' AND l.MD_LOC_ISACTIVE = 1 AND lp.MD_LOC_STAT_ID = ' . MdLocStat::L_OPEN;
            $eligible = $regular;
            if ($includeService) {
                $service = 'l.MD_LOC_TYPE_ID = ' . MdLocType::SERVICE . ' AND ((l.MD_LOC_ISACTIVE = 1 AND lp.MD_LOC_STAT_ID = '
                    . MdLocStat::SERVICE_OPEN . ') OR ' . self::serviceEquipmentExistsSql('l.MD_LOC_ID') . ')';
                $eligible = '(' . $regular . ') OR (' . $service . ')';
            }
            $qry .= ' WHERE l.MD_LOC_ID = lp.MD_LOC_ID AND (' . $eligible . ')';
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

			$qry .= " LIMIT :offset, :rowno";

			$statement = $db->prepare($qry);
            $statement->bindValue(":offset", $offset, PDO::PARAM_INT);
            $statement->bindValue(":rowno", $rowno, PDO::PARAM_INT);

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
			$qry .= '       ,prnt.MD_LOC_TYPE_ID AS parentTypeId';
			
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


            try {
                $statement = $db->prepare($qry);
                if (!$statement || !$statement->bindParam(":uid", $uid, PDO::PARAM_INT) || !$statement->execute()) {
                    return null;
                }
                $res = $statement->fetchAll(PDO::FETCH_ASSOC);
                return is_array($res) ? $res : null;
            } catch (PDOException $error) {
                return null;
            }


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

            try {
                $statement = $db->prepare($qry);
                if (!$statement || !$statement->bindParam(":uid", $uid, PDO::PARAM_INT) || !$statement->execute()) {
                    return null;
                }
                $res = $statement->fetchAll(PDO::FETCH_ASSOC);
                return is_array($res) ? $res : null;
            } catch (PDOException $error) {
                return null;
            }


		} // getUserTransportsReturnList

		public static function getTEqTrnsWithLoc($db, $eid, $df, $dt) {

			$qry =  " SELECT ";
			$qry .= "        t.".TEqTrns::$id;
			$qry .= "       ,t.".TEqTrns::$time;
			$qry .= "       ,t.".TEqTrns::$isLast;
			$qry .= "       ,t.".TEqTrns::$isTeleport;
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

			$qry .= " ORDER BY  t.".TEqTrns::$time.", t.".TEqTrns::$id;

			$statement = $db->prepare($qry);
			if (!$statement) { return null; }

			$statement->bindParam(":eid", $eid, PDO::PARAM_INT);
			if ($df!= null){
				$statement->bindParam(":df", $df, PDO::PARAM_STR);
			}
			if ($dt!= null){
				$statement->bindParam(":dt", $dt, PDO::PARAM_STR);
			}
			if (!$statement->execute()) { return null; }

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

		public static function canTakeEquipment($db, $eid, $uid, $lid) {


			$qry =  " SELECT ";
			$qry .= "        e.".MdEq::$id;

			$qry .= " FROM ";
			$qry .= "        ".MdEq::$table." as e," ;
			$qry .= "        ".TEqProp::$table." as ep," ;
			$qry .= "        ".MdLoc::$table." as l," ;
			$qry .= "        ".MdLoc::$table." as dest," ;
			$qry .= "        ".TLocProp::$table." as dp" ;

			$qry .= " WHERE ";

			$qry .= "        l.".MdLoc::$id." = ep.".TEqProp::$locId." " ;
			$qry .= " AND    e.".MdEq::$id." = ep.".TEqProp::$equipmentId." " ;
			$qry .= " AND    l.".MdLoc::$typeId." in ".MdLocType::getTypesForSQL([MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE]) ;
			$qry .= " AND    l.".MdLoc::$isActive." = 1" ;
			$qry .= " AND    e.".MdEq::$id." = :i";
			$qry .= " AND    e.".MdEq::$isActive." = 1";
			// Loading follows ownership of an active, taken transport at the equipment's source.
			$qry .= " AND    dest.".MdLoc::$id." = :lid";
			$qry .= " AND    dest.".MdLoc::$typeId." = ".MdLocType::TRANSPORT;
			$qry .= " AND    dest.".MdLoc::$isActive." = 1";
			$qry .= " AND    dp.".TLocProp::$locId." = dest.".MdLoc::$id;
			$qry .= " AND    dp.".TLocProp::$locStatId." = ".MdLocStat::T_TAKEN;
			$qry .= " AND    dp.".TLocProp::$userId." = :uid";
			$qry .= " AND    dp.".TLocProp::$locIdPrnt." = ep.".TEqProp::$locId;



			$statement = $db->prepare($qry);
			
			$statement->bindParam(":i", $eid, PDO::PARAM_INT);
			$statement->bindParam(":uid", $uid, PDO::PARAM_INT);
			$statement->bindParam(":lid", $lid, PDO::PARAM_INT);
			
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

        public static function isUpdateUUID($value) {
            return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', $value);
        }

        // Status codes distinguish a missing target from a successful unchanged assignment.
        private static function updateExactUUID($db, $table, $idColumn, $uuidColumn, $uuid, $values) {
            if (!self::isUpdateUUID($uuid)) { return 400; }
            if ($db->inTransaction()) { return 500; }
            try {
                if (!$db->beginTransaction()) { return 500; }
                $statement = $db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $uuidColumn . ' = :uuid FOR UPDATE');
                $statement->bindValue(':uuid', $uuid, PDO::PARAM_STR);
                if (!$statement->execute()) { $db->rollBack(); return 500; }
                $targets = $statement->fetchAll(PDO::FETCH_ASSOC);
                if (count($targets) !== 1) { $db->rollBack(); return count($targets) === 0 ? 404 : 409; }
                $target = $targets[0];
                if ($table === MdLoc::$table && (int) $target[MdLoc::$typeId] === MdLocType::SERVICE) {
                    $db->rollBack();
                    return 409;
                }
                if ($table === MdEq::$table && !self::equipmentEditAllowed($db, $target, $values)) {
                    $db->rollBack();
                    return 409;
                }
                $assignments = array();
                foreach ($values as $column => $value) { $assignments[] = $column . ' = :' . $column; }
                $statement = $db->prepare('UPDATE ' . $table . ' SET ' . implode(', ', $assignments) . ' WHERE ' . $idColumn . ' = :id');
                foreach ($values as $column => $value) {
                    $statement->bindValue(':' . $column, $value[0], $value[1]);
                }
                $statement->bindValue(':id', $targets[0][$idColumn], PDO::PARAM_INT);
                if (!$statement->execute()) { $db->rollBack(); return 500; }
                if (!$db->commit()) { if ($db->inTransaction()) { $db->rollBack(); } return 500; }
                return 200;
            } catch (\Throwable $error) {
                try { if ($db->inTransaction()) { $db->rollBack(); } } catch (\Throwable $rollbackError) { /* Return only the controlled status. */ }
                return 500;
            }
        }

        private static function equipmentEditAllowed($db, $target, $values) {
            if ((int) $target[MdEq::$primaryTypeId] === $values[MdEq::$primaryTypeId][0]
                && (int) $target[MdEq::$secondaryTypeId] === $values[MdEq::$secondaryTypeId][0]
                && (int) $target[MdEq::$isActive] === (int) $values[MdEq::$isActive][0]) { return true; }

            // The equipment master is already locked, before its current property as in movements.
            $statement = $db->prepare('SELECT * FROM t_eq_prop WHERE MD_EQ_ID = :id FOR UPDATE');
            if (!$statement || !$statement->bindValue(':id', (int) $target[MdEq::$id], PDO::PARAM_INT)
                || !$statement->execute()) {
                throw new \RuntimeException('Equipment property lock failed');
            }
            $properties = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return count($properties) <= 1
                && (int) ($properties[0][TEqProp::$serviceStatId] ?? MdEqServiceStat::READY) !== MdEqServiceStat::IN_SERVICE;
        }

        public static function updateEqByUUID($db, $uuid, $code, $name, $primaryTypeId, $secondaryTypeId, $isActive) {
            return self::updateExactUUID($db, MdEq::$table, MdEq::$id, MdEq::$uuid, $uuid, array(
                MdEq::$code => array($code, PDO::PARAM_STR), MdEq::$name => array($name, PDO::PARAM_STR),
                MdEq::$primaryTypeId => array((int) $primaryTypeId, PDO::PARAM_INT),
                MdEq::$secondaryTypeId => array((int) $secondaryTypeId, PDO::PARAM_INT), MdEq::$isActive => array($isActive, PDO::PARAM_INT)));
        }

        public static function updateLocByUUID($db, $uuid, $code, $name, $desc, $isActive) {
            return self::updateExactUUID($db, MdLoc::$table, MdLoc::$id, MdLoc::$uuid, $uuid, array(
                MdLoc::$code => array($code, PDO::PARAM_STR), MdLoc::$name => array($name, PDO::PARAM_STR),
                MdLoc::$desc => array($desc, PDO::PARAM_STR), MdLoc::$isActive => array($isActive, PDO::PARAM_INT)));
        }

	}

	class Transaction {

		public static function createEquipment($db, $data, $lid, $primaryTypeId, $secondaryTypeId, $uid){

			try {
				$db->beginTransaction();

				$qry = "";
				$qry .=  " INSERT INTO ";
				$qry .=  "  ".MdEq::$table. " ( ";
			
				$qry .=  "  ".MdEq::$uuid;
				$qry .=  " ,".MdEq::$code;
				$qry .=  " ,".MdEq::$name;
				$qry .=  " ,".MdEq::$isActive;
				$qry .=  " ,".MdEq::$primaryTypeId;
				$qry .=  " ,".MdEq::$secondaryTypeId;
                $warrantyDate = $data[APIMgmt::WARRANTY_DATE] ?? null;
                if ($warrantyDate !== null) { $qry .= ' ,' . MdEq::$warrantyDate; }
				$qry .=  " ) ";
			
				$qry .=  " VALUES ( " ;
			
				$qry .=  "  :uuid ";
				$qry .=  " ,:code ";
				$qry .=  " ,:name ";
				$qry .=  " ,:ia ";
				$qry .=  " ,:primary_type ";
				$qry .=  " ,:secondary_type ";
                if ($warrantyDate !== null) { $qry .= ' ,:warranty_date'; }
				$qry .=  " ); ";


				$statement = $db->prepare($qry);
				$statement->bindParam(":uuid", $data[APIMgmt::UUID] , PDO::PARAM_STR);
				$statement->bindParam(":code", $data[APIMgmt::CODE] , PDO::PARAM_STR);
				$statement->bindParam(":name", $data[APIMgmt::NAME] , PDO::PARAM_STR);
				$statement->bindParam(":ia", $data[APIMgmt::ISACTIVE] , PDO::PARAM_INT);
				$statement->bindParam(":primary_type", $primaryTypeId, PDO::PARAM_INT);
				$statement->bindParam(":secondary_type", $secondaryTypeId, PDO::PARAM_INT);
                if ($warrantyDate !== null) { $statement->bindValue(':warranty_date', $warrantyDate, PDO::PARAM_STR); }
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

        // Movement locks are always acquired by table, then ascending numeric ID:
        // md_loc -> t_loc_prop -> md_eq -> t_eq_prop. Validate only these current reads.
        private static function lockMovementRows($db, $table, $column, $ids, $skipAmbiguous = false) {
            $ids = array_unique(array_map('intval', $ids));
            sort($ids, SORT_NUMERIC);
            $result = array();
            foreach ($ids as $id) {
                if ($id < 1) { return false; }
                $statement = $db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $column . ' = :id FOR UPDATE');
                if (!$statement || !$statement->bindValue(':id', $id, PDO::PARAM_INT) || !$statement->execute()) {
                    throw new \RuntimeException('Movement lock failed');
                }
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                $statement->closeCursor();
                if (!is_array($rows)) { throw new \RuntimeException('Movement read failed'); }
                if (count($rows) > 1 && !$skipAmbiguous) { return false; }
                $result[$id] = count($rows) === 1 ? $rows[0] : false;
            }
            return $result;
        }

        private static function lockMovementLocations($db, $ids) {
            $locations = self::lockMovementRows($db, MdLoc::$table, MdLoc::$id, $ids);
            if ($locations === false || in_array(false, $locations, true)) { return false; }
            $properties = self::lockMovementRows($db, TLocProp::$table, TLocProp::$locId, $ids);
            return $properties === false ? false : array('locations' => $locations, 'properties' => $properties);
        }

        private static function isMovementId($id) {
            return (is_int($id) || is_string($id))
                && filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) !== false;
        }

        private static function movementOwnerAllowed($property, $actor) {
            return is_array($actor) && isset($actor[SyUser::$id], $actor[SyUser::$aclId])
                && ($property[TLocProp::$userId] == $actor[SyUser::$id]
                    || SyUserAcl::hasPrivilege($actor, SyUserAcl::MANAGER));
        }

        private static function rejectMovement($db) {
            if (!$db->rollBack()) { throw new \RuntimeException('Movement rollback failed'); }
            return 409;
        }

        private static function failMovement($db) {
            try { if ($db->inTransaction()) { $db->rollBack(); } } catch (\Throwable $ignored) { }
            return 500;
        }

        private static function requireInnoDB($db, $tables) {
            $placeholders = implode(',', array_fill(0, count($tables), '?'));
            $statement = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'InnoDB' AND TABLE_NAME IN ($placeholders)");
            if (!$statement || !$statement->execute($tables) || (int) $statement->fetchColumn() !== count($tables)) {
                throw new \RuntimeException('This operation requires InnoDB tables');
            }
        }

        private static function writeOne($statement, $values) {
            if (!$statement || !$statement->execute($values) || $statement->rowCount() !== 1) {
                throw new \RuntimeException('Database write failed');
            }
        }

        private static function checklistConflict($db, $reason, $lineIndexes = array()) {
            return array('status' => self::rejectMovement($db),
                'data' => array('reason' => $reason, 'lineIndexes' => $lineIndexes));
        }

        private static function getLockedChecklistStock($db, $warehouseId) {
            // Warehouse movement holds the same location lock. Include inactive
            // candidates so concurrent master-data edits are evaluated on locked rows.
            $statement = $db->prepare('SELECT DISTINCT MD_EQ_ID FROM t_eq_prop WHERE MD_LOC_ID = ? AND MD_EQ_ID > 1');
            if (!$statement || !$statement->execute(array($warehouseId))) {
                throw new \RuntimeException('Checklist inventory read failed');
            }
            $ids = $statement->fetchAll(PDO::FETCH_COLUMN);
            $items = self::lockMovementRows($db, MdEq::$table, MdEq::$id, $ids);
            $properties = self::lockMovementRows($db, TEqProp::$table, TEqProp::$equipmentId, $ids, true);
            $stock = array();
            $validPairs = array();
            foreach ($items as $id => $item) {
                if (!$item || (int) $item[MdEq::$isActive] !== 1 || !$properties[$id]
                    || (int) $properties[$id][TEqProp::$locId] !== $warehouseId
                    || (int) $properties[$id][TEqProp::$serviceStatId] === MdEqServiceStat::IN_SERVICE) { continue; }
                $primary = (int) $item[MdEq::$primaryTypeId];
                $secondary = (int) $item[MdEq::$secondaryTypeId];
                $pair = $primary . ':' . $secondary;
                $validPairs[$pair] ??= MdEqTypeSecondary::matchesPrimary($db, $primary, $secondary);
                if ($validPairs[$pair]) { $stock[$id] = array($primary, $secondary); }
            }
            return $stock;
        }

        private static function checklistStockConflicts($stock, $lines) {
            $capacity = array();
            foreach ($stock as list($primary, $secondary)) {
                foreach (array((string) $primary, $primary . ':' . $secondary) as $key) {
                    $capacity[$key] = ($capacity[$key] ?? 0) + 1;
                }
            }
            $demand = $groups = $exactItems = $conflicts = array();
            foreach ($lines as $index => $line) {
                $primary = $line['primary_type_id'];
                $secondary = $line['secondary_type_id'];
                $equipment = $line['equipment_id'];
                $keys = array((string) $primary);
                if ($secondary !== null) { $keys[] = $primary . ':' . $secondary; }
                foreach ($keys as $key) {
                    $demand[$key] = ($demand[$key] ?? 0) + $line['quantity'];
                    $groups[$key][] = $index;
                }
                if ($equipment !== null) {
                    if (($stock[$equipment] ?? null) !== array($primary, $secondary)) { $conflicts[] = $index; }
                    if (isset($exactItems[$equipment])) {
                        $conflicts[] = $exactItems[$equipment];
                        $conflicts[] = $index;
                    }
                    $exactItems[$equipment] = $index;
                }
            }
            foreach ($demand as $key => $quantity) {
                if ($quantity > ($capacity[$key] ?? 0)) { $conflicts = array_merge($conflicts, $groups[$key]); }
            }
            $conflicts = array_values(array_unique($conflicts));
            sort($conflicts, SORT_NUMERIC);
            return $conflicts;
        }

        public static function createChecklist($db, $authorId, $warehouseId, $assigneeId, $lines) {
            if (!$db || $db->inTransaction()) { return array('status' => 500); }
            try {
                self::requireInnoDB($db, array(MdLoc::$table, TLocProp::$table, MdEq::$table,
                    TEqProp::$table, TEqChecklist::$table, TEqChecklistItem::$table));
                if (!$db->beginTransaction()) { throw new \RuntimeException('Checklist transaction failed'); }
                $locked = self::lockMovementLocations($db, array($warehouseId));
                if (!$locked || !ReadData::isChecklistWarehouse($locked['locations'][$warehouseId])) {
                    return self::checklistConflict($db, 'warehouse');
                }
                $stock = self::getLockedChecklistStock($db, $warehouseId);
                if (!SyUser::getActiveSyUser($db, $assigneeId)) {
                    return self::checklistConflict($db, 'assignee');
                }
                $conflicts = self::checklistStockConflicts($stock, $lines);
                if ($conflicts) { return self::checklistConflict($db, 'stock', $conflicts); }

                $statement = $db->prepare('INSERT INTO t_eq_checklist (SY_USER_ID_AUTHOR, SY_USER_ID_ASSIGNEE, MD_LOC_ID)
                    VALUES (?, ?, ?)');
                self::writeOne($statement, array($authorId, $assigneeId, $warehouseId));
                $id = (int) $db->lastInsertId();
                if ($id < 1) { throw new \RuntimeException('Checklist ID unavailable'); }
                $statement = $db->prepare('INSERT INTO t_eq_checklist_item (T_EQ_CHECKLIST_ID, MD_EQ_TYPE_PRIMARY_ID,
                    MD_EQ_TYPE_SECONDARY_ID, MD_EQ_ID, T_EQ_CHECKLIST_ITEM_AMOUNT) VALUES (?, ?, ?, ?, ?)');
                foreach ($lines as $line) {
                    self::writeOne($statement, array($id, $line['primary_type_id'], $line['secondary_type_id'],
                        $line['equipment_id'], $line['quantity']));
                }
                if (!$db->commit()) { throw new \RuntimeException('Checklist commit failed'); }
                return array('status' => 200, 'data' => array('id' => $id));
            } catch (\Throwable $error) {
                return array('status' => self::failMovement($db));
            }
        }

        private static function equipmentTakeAllowed($from, $to, $transportProperty, $uid, $lfid) {
            return in_array($from[MdLoc::$typeId], array(MdLocType::WAREHOUSE, MdLocType::CONSTRUCTION_SITE))
                && $from[MdLoc::$isActive] == 1 && $to[MdLoc::$typeId] == MdLocType::TRANSPORT
                && $to[MdLoc::$isActive] == 1 && $transportProperty
                && $transportProperty[TLocProp::$locStatId] == MdLocStat::T_TAKEN
                && $transportProperty[TLocProp::$userId] == $uid && $transportProperty[TLocProp::$locIdPrnt] == $lfid;
        }

        private static function checklistCreditLine($lines, $item) {
            foreach ($lines as $index => $line) {
                if ($line['remaining'] > 0 && $line['typeId'] === (int) $item[MdEq::$primaryTypeId]
                    && ($line['secondaryTypeId'] === null || $line['secondaryTypeId'] === (int) $item[MdEq::$secondaryTypeId])
                    && ($line['equipmentId'] === null || $line['equipmentId'] === (int) $item[MdEq::$id])) {
                    return $index;
                }
            }
            return null;
        }

        public static function takeWithChecklist($db, $userId, $checklistId, $fromUUID, $toUUID, $uuids) {
            if (!$db || $db->inTransaction()) { return array('status' => 500); }
            try {
                self::requireInnoDB($db, array(MdLoc::$table, TLocProp::$table, MdEq::$table,
                    TEqProp::$table, TEqTrns::$table, TEqChecklist::$table, TEqChecklistItem::$table, TEqChecklistItemTrns::$table));
                if (!$db->beginTransaction()) { throw new \RuntimeException('Checklist transaction failed'); }
                $headers = self::lockMovementRows($db, TEqChecklist::$table, TEqChecklist::$id, array($checklistId));
                $header = $headers[$checklistId] ?? false;
                if (!$header || (int) $header[TEqChecklist::$assigneeId] !== $userId) {
                    self::rejectMovement($db);
                    return array('status' => 404);
                }
                if ((int) $header[TEqChecklist::$isClosed] !== 0) { return self::checklistConflict($db, 'closed'); }

                $from = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $fromUUID);
                $to = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $toUUID);
                $fromId = (int) ($from[0][MdLoc::$id] ?? 0);
                $toId = (int) ($to[0][MdLoc::$id] ?? 0);
                if (count($from) !== 1 || count($to) !== 1 || $fromId <= 1 || $toId <= 1 || $fromId === $toId
                    || $fromId !== (int) $header[MdLoc::$id]) { return self::checklistConflict($db, 'transport'); }
                $locked = self::lockMovementLocations($db, array($fromId, $toId));
                if (!$locked || $locked['locations'][$fromId][MdLoc::$uuid] !== $fromUUID
                    || $locked['locations'][$toId][MdLoc::$uuid] !== $toUUID
                    || !ReadData::isChecklistWarehouse($locked['locations'][$fromId])
                    || !self::equipmentTakeAllowed($locked['locations'][$fromId],
                        $locked['locations'][$toId], $locked['properties'][$toId], $userId, $fromId)) {
                    return self::checklistConflict($db, 'transport');
                }

                $ids = array();
                foreach ($uuids as $index => $uuid) {
                    $matches = self::findMovementUUID($db, MdEq::$table, MdEq::$uuid, $uuid);
                    if (count($matches) === 1 && $matches[0][MdEq::$uuid] === $uuid
                        && (int) $matches[0][MdEq::$id] > 1) {
                        $ids[$index] = (int) $matches[0][MdEq::$id];
                    }
                }
                $items = self::lockMovementRows($db, MdEq::$table, MdEq::$id, $ids);
                $properties = self::lockMovementRows($db, TEqProp::$table, TEqProp::$equipmentId, $ids, true);
                $lines = ReadData::getChecklistLines($db, $checklistId);
                if (!$lines) { throw new \RuntimeException('Checklist lines unavailable'); }
                $mapping = $db->prepare('INSERT INTO t_eq_checklist_item_trns (MD_EQ_ID, T_EQ_CHECKLIST_ITEM_ID, T_EQ_TRNS_ID)
                    VALUES (?, ?, ?)');
                $results = array();
                $moved = false;
                foreach ($uuids as $index => $uuid) {
                    $id = $ids[$index] ?? 0;
                    $item = $items[$id] ?? false;
                    $property = $properties[$id] ?? false;
                    $result = array('uuid' => $uuid, 'success' => 0, 'lineId' => null);
                    if ($item && $property && $item[MdEq::$uuid] === $uuid && (int) $item[MdEq::$isActive] === 1
                        && (int) $property[TEqProp::$locId] === $fromId
                        && (int) $property[TEqProp::$serviceStatId] !== MdEqServiceStat::IN_SERVICE) {
                        $movementId = self::writeEquipmentMovement($db, $id, $userId, $fromId, $toId, captureId: true);
                        if ($movementId < 1) { throw new \RuntimeException('Movement ID unavailable'); }
                        $result['success'] = 1;
                        $moved = true;
                        $lineIndex = self::checklistCreditLine($lines, $item);
                        if ($lineIndex !== null) {
                            $line = &$lines[$lineIndex];
                            self::writeOne($mapping, array($id, $line['id'], $movementId));
                            $line['remaining']--;
                            $result['lineId'] = $line['id'];
                            unset($line);
                        }
                    }
                    $results[] = $result;
                }
                if ($moved) {
                    $statement = $db->prepare('UPDATE t_eq_checklist SET T_EQ_CHECKLIST_ISCLOSED = 1 WHERE T_EQ_CHECKLIST_ID = ?');
                    self::writeOne($statement, array($checklistId));
                }
                $fulfillment = array();
                foreach ($lines as $line) {
                    $fulfillment[] = array('id' => $line['id'], 'quantity' => $line['quantity'],
                        'taken' => $line['quantity'] - $line['remaining'], 'remaining' => $line['remaining']);
                }
                if (!$db->commit()) { throw new \RuntimeException('Checklist commit failed'); }
                return array('status' => 200, 'data' => array('id' => $checklistId,
                    'isClosed' => $moved ? 1 : 0, 'results' => $results, 'lines' => $fulfillment));
            } catch (\Throwable $error) {
                return array('status' => self::failMovement($db));
            }
        }

        private static function serviceWrite($db, $sql, $values) {
            $statement = $db->prepare($sql);
            if (!$statement || !$statement->execute($values)) {
                throw new \RuntimeException('Service write failed');
            }
        }

        public static function saveServiceLocation($db, $data, $secondaryTypeIds, $actorId, $create) {
            if (!$db || $db->inTransaction()) { return 500; }
            try {
                self::requireInnoDB($db, array(MdLoc::$table, TLocProp::$table, TLocServiceEqTypes::$table));
                if (!$db->beginTransaction()) { return 500; }
                $lookup = 'SELECT MD_LOC_ID FROM md_loc WHERE MD_LOC_UUID = ?';
                $statement = $db->prepare($create ? $lookup . ' FOR UPDATE' : $lookup);
                if (!$statement || !$statement->execute(array($data[APIMgmt::UUID]))) {
                    throw new \RuntimeException('Service location lookup failed');
                }
                $matches = $statement->fetchAll(PDO::FETCH_COLUMN);
                $statement->closeCursor();
                if ($create && $matches) { return self::rejectMovement($db); }
                if (!$create && count($matches) !== 1) {
                    self::rejectMovement($db);
                    return $matches ? 409 : 404;
                }
                $master = array($data[APIMgmt::CODE], $data[APIMgmt::NAME], $data[APIMgmt::DESC], (int) $data[APIMgmt::ISACTIVE]);
                $statusId = (int) $data[APIMgmt::SERVICE_LOCATION_STATUS];
                if ($create) {
                    self::serviceWrite($db, 'INSERT INTO md_loc (MD_LOC_CODE, MD_LOC_NAME, MD_LOC_DESC, MD_LOC_ISACTIVE,
                        MD_LOC_UUID, MD_LOC_TYPE_ID) VALUES (?, ?, ?, ?, ?, ?)',
                        array_merge($master, array($data[APIMgmt::UUID], MdLocType::SERVICE)));
                    $locationId = (int) $db->lastInsertId();
                    if ($locationId < 2) { throw new \RuntimeException('Service location ID unavailable'); }
                    self::serviceWrite($db, 'INSERT INTO t_loc_prop (MD_LOC_ID, MD_LOC_STAT_ID, SY_USER_ID) VALUES (?, ?, ?)',
                        array($locationId, $statusId, $actorId));
                } else {
                    $locationId = (int) $matches[0];
                    $locked = self::lockMovementLocations($db, array($locationId));
                    if (!$locked || !$locked['properties'][$locationId]
                        || $locked['locations'][$locationId][MdLoc::$uuid] !== $data[APIMgmt::UUID]
                        || (int) $locked['locations'][$locationId][MdLoc::$typeId] !== MdLocType::SERVICE) {
                        return self::rejectMovement($db);
                    }
                    self::serviceWrite($db, 'UPDATE md_loc SET MD_LOC_CODE = ?, MD_LOC_NAME = ?, MD_LOC_DESC = ?,
                        MD_LOC_ISACTIVE = ? WHERE MD_LOC_ID = ?', array_merge($master, array($locationId)));
                    self::serviceWrite($db, 'UPDATE t_loc_prop SET MD_LOC_STAT_ID = ?, SY_USER_ID = ?,
                        T_LOC_PROP_TIME = NOW() WHERE MD_LOC_ID = ?', array($statusId, $actorId, $locationId));
                    self::serviceWrite($db, 'DELETE FROM t_loc_service_eq_types WHERE MD_LOC_ID = ?', array($locationId));
                }
                $insert = $db->prepare('INSERT INTO t_loc_service_eq_types (MD_LOC_ID, MD_EQ_TYPE_SECONDARY_ID) VALUES (?, ?)');
                foreach ($secondaryTypeIds as $secondaryTypeId) {
                    self::writeOne($insert, array($locationId, $secondaryTypeId));
                }
                if (!$db->commit()) { throw new \RuntimeException('Service setup commit failed'); }
                return 200;
            } catch (\Throwable $error) {
                return self::failMovement($db);
            }
        }

        public static function transferServiceEquipment($db, $userId, $fromUuid, $toUuid, $item, $pickup) {
            if (!$db || $db->inTransaction()) { return 500; }
            try {
                $from = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $fromUuid);
                $to = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $toUuid);
                $equipment = self::findMovementUUID($db, MdEq::$table, MdEq::$uuid, $item[APIMgmt::UUID]);
                if (count($from) !== 1 || count($to) !== 1 || count($equipment) !== 1) { return 409; }
                $fromId = (int) $from[0][MdLoc::$id];
                $toId = (int) $to[0][MdLoc::$id];
                $equipmentId = (int) $equipment[0][MdEq::$id];
                if (min($fromId, $toId, $equipmentId) <= 1 || $fromId === $toId) { return 409; }

                if (!$db->beginTransaction()) { return 500; }
                $locked = self::lockMovementLocations($db, array($fromId, $toId));
                if (!$locked || $locked['locations'][$fromId][MdLoc::$uuid] !== $fromUuid
                    || $locked['locations'][$toId][MdLoc::$uuid] !== $toUuid) { return self::rejectMovement($db); }
                $serviceId = $pickup ? $fromId : $toId;
                $transportId = $pickup ? $toId : $fromId;
                $service = $locked['locations'][$serviceId];
                $serviceProperty = $locked['properties'][$serviceId];
                if ((int) $service[MdLoc::$typeId] !== MdLocType::SERVICE || !$serviceProperty
                    || !ReadData::serviceTransportAllowed($locked['locations'][$transportId],
                        $locked['properties'][$transportId], $userId, $serviceId)
                    || (!$pickup && !ReadData::serviceDropOffAllowed($service, $serviceProperty))) {
                    return self::rejectMovement($db);
                }
                $equipmentRows = self::lockMovementRows($db, MdEq::$table, MdEq::$id, array($equipmentId));
                $propertyRows = self::lockMovementRows($db, TEqProp::$table, TEqProp::$equipmentId, array($equipmentId));
                $equipment = $equipmentRows[$equipmentId] ?? false;
                $property = $propertyRows[$equipmentId] ?? false;
                if (!$equipment || !$property || $equipment[MdEq::$uuid] !== $item[APIMgmt::UUID]
                    || (int) $equipment[MdEq::$isActive] !== 1 || (int) $property[TEqProp::$locId] !== $fromId) {
                    return self::rejectMovement($db);
                }
                $currentStatus = (int) $property[TEqProp::$serviceStatId];
                if ($pickup) {
                    if ($currentStatus !== MdEqServiceStat::IN_SERVICE) { return self::rejectMovement($db); }
                    self::serviceWrite($db, 'UPDATE t_eq_service SET T_EQ_SERVICE_ISLAST = 0
                        WHERE MD_EQ_ID = ? AND T_EQ_SERVICE_ISLAST = 1', array($equipmentId));
                    self::serviceWrite($db, 'INSERT INTO t_eq_service (MD_EQ_ID, T_EQ_SERVICE_WARRANTY_DATE, T_EQ_SERVICE_ISBROKEN)
                        VALUES (?, ?, ?)', array($equipmentId, $item[APIMgmt::WARRANTY_DATE], $item[APIMgmt::IS_BROKEN]));
                    $nextStatus = $item[APIMgmt::IS_BROKEN] ? MdEqServiceStat::BROKEN : MdEqServiceStat::READY;
                } else {
                    if (!in_array($currentStatus, array(MdEqServiceStat::NEEDS_SERVICE, MdEqServiceStat::BROKEN), true)) {
                        return self::rejectMovement($db);
                    }
                    $capability = $db->prepare('SELECT 1 FROM t_loc_service_eq_types
                        WHERE MD_LOC_ID = ? AND MD_EQ_TYPE_SECONDARY_ID = ? LIMIT 1');
                    if (!$capability || !$capability->execute(array($serviceId, $equipment[MdEq::$secondaryTypeId]))) {
                        throw new \RuntimeException('Service capability lookup failed');
                    }
                    if (!$capability->fetchColumn()) { return self::rejectMovement($db); }
                    $nextStatus = MdEqServiceStat::IN_SERVICE;
                }
                self::writeEquipmentMovement($db, $equipmentId, $userId, $fromId, $toId);
                self::serviceWrite($db, 'UPDATE t_eq_prop SET MD_EQ_SERVICE_STAT_ID = ? WHERE MD_EQ_ID = ?',
                    array($nextStatus, $equipmentId));
                if (!$db->commit()) { throw new \RuntimeException('Service transfer commit failed'); }
                return 200;
            } catch (\Throwable $error) {
                return self::failMovement($db);
            }
        }

        public static function verifyServiceTransferEngines($db, $pickup) {
            $tables = array(MdLoc::$table, TLocProp::$table, MdEq::$table, TEqProp::$table, TEqTrns::$table,
                $pickup ? TEqService::$table : TLocServiceEqTypes::$table);
            self::requireInnoDB($db, $tables);
        }

		public static function locationStatusChange($db, $lid, $uid, $stat_f, $stat_t, $actor){

            if (!(self::isMovementId($lid) && self::isMovementId($uid))) { return 409; }
            if ($db->inTransaction()) { return 500; }

			try {
                if (!$db->beginTransaction()) { throw new \RuntimeException('Movement transaction failed'); }
                // This unlocked read only discovers the parent ID for deterministic lock ordering.
                // The locked property below must still reference that parent, otherwise reject.
                $hint = TLocProp::getTLocPropByLocId($db, $lid);
                if (!$hint) { return self::rejectMovement($db); }
                $parentId = $hint[TLocProp::$locIdPrnt];
                $locked = self::lockMovementLocations($db, array($lid, $parentId));
                if (!$locked || !$locked['properties'][$lid]) { return self::rejectMovement($db); }
                $transport = $locked['locations'][$lid];
                $property = $locked['properties'][$lid];
                $parent = $locked['locations'][$parentId];
                $taking = $stat_f == MdLocStat::T_PARKED && $stat_t == MdLocStat::T_TAKEN;
                $returning = $stat_f == MdLocStat::T_TAKEN && $stat_t == MdLocStat::T_PARKED;
                $valid = $transport[MdLoc::$typeId] == MdLocType::TRANSPORT && $transport[MdLoc::$isActive] == 1
                    && $property[TLocProp::$locStatId] == $stat_f && $property[TLocProp::$locIdPrnt] == $parentId
                    && ($taking || ($returning && $parent[MdLoc::$typeId] == MdLocType::WAREHOUSE
                        && self::movementOwnerAllowed($property, $actor)));
                // Returning a loaded transport does not alter its equipment or parent.
                if (!$valid) { return self::rejectMovement($db); }



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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
                if ($statement->rowCount() !== 1) { throw new \RuntimeException('Movement property update failed'); }
				$statement->closeCursor();

                if (!$db->commit()) { throw new \RuntimeException('Movement commit failed'); }
                return 200;

            } catch (\Throwable $error) {
                return self::failMovement($db);
            }


		}



        private static function writeEquipmentMovement($db, $eid, $uid, $lfid, $ltid, $isTeleport = false, $captureId = false) {
            if ($isTeleport) {
                // Verify the migration before clearing any history flags.
                $statement = $db->prepare('SELECT ' . TEqTrns::$isTeleport . ' FROM ' . TEqTrns::$table . ' LIMIT 0');
                if (!$statement || !$statement->execute()) { throw new \RuntimeException('Movement flag unavailable'); }
                $statement->closeCursor();
            }
            $statement = $db->prepare('UPDATE ' . TEqTrns::$table . ' SET ' . TEqTrns::$isLast . ' = 0'
                . ' WHERE ' . TEqTrns::$equipmentId . ' = :eid AND ' . TEqTrns::$isLast . ' = 1');
            $statement->bindParam(':eid', $eid, PDO::PARAM_INT);
            if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
            $statement->closeCursor();

            $columns = array(TEqTrns::$equipmentId, TEqTrns::$locIdFrom, TEqTrns::$locIdTo, TEqTrns::$userId);
            $values = array(':eid', ':lfid', ':ltid', ':uid');
            if ($isTeleport) {
                $columns[] = TEqTrns::$isTeleport;
                $values[] = '1';
            }
            $statement = $db->prepare('INSERT INTO ' . TEqTrns::$table . ' (' . implode(', ', $columns)
                . ') VALUES (' . implode(', ', $values) . ')');
            $statement->bindParam(':eid', $eid, PDO::PARAM_INT);
            $statement->bindParam(':lfid', $lfid, PDO::PARAM_INT);
            $statement->bindParam(':ltid', $ltid, PDO::PARAM_INT);
            $statement->bindParam(':uid', $uid, PDO::PARAM_INT);
            if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
            $movementId = $captureId ? (int) $db->lastInsertId() : null;
            $statement->closeCursor();

            $statement = $db->prepare('UPDATE ' . TEqProp::$table . ' SET ' . TEqProp::$userId . ' = :uid, '
                . TEqProp::$time . ' = now(), ' . TEqProp::$locId . ' = :ltid'
                . ' WHERE ' . TEqProp::$equipmentId . ' = :eid');
            $statement->bindParam(':eid', $eid, PDO::PARAM_INT);
            $statement->bindParam(':ltid', $ltid, PDO::PARAM_INT);
            $statement->bindParam(':uid', $uid, PDO::PARAM_INT);
            if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
            if ($statement->rowCount() !== 1) { throw new \RuntimeException('Movement property update failed'); }
            $statement->closeCursor();
            return $movementId;
        }

        private static function findMovementUUID($db, $table, $column, $uuid) {
            $statement = $db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $column . ' = :uuid LIMIT 2');
            if (!$statement || !$statement->bindValue(':uuid', $uuid, PDO::PARAM_STR) || !$statement->execute()) {
                throw new \RuntimeException('Movement lookup failed');
            }
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return $rows;
        }

        public static function teleportEquipment($db, $actor, $equipmentUUID, $fromUUID, $toUUID) {
            if (!isset($actor[SyUser::$id], $actor[SyUser::$aclId])
                || !SyUserAcl::hasPrivilege($actor, SyUserAcl::MANAGER)) { return 403; }
            if ($db->inTransaction()) { return 500; }

            try {
                $equipment = self::findMovementUUID($db, MdEq::$table, MdEq::$uuid, $equipmentUUID);
                $from = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $fromUUID);
                $to = self::findMovementUUID($db, MdLoc::$table, MdLoc::$uuid, $toUUID);
                if (!$equipment || !$from || !$to) { return 404; }
                if (count($equipment) !== 1 || count($from) !== 1 || count($to) !== 1) { return 409; }
                $eid = (int) $equipment[0][MdEq::$id];
                $lfid = (int) $from[0][MdLoc::$id];
                $ltid = (int) $to[0][MdLoc::$id];
                if ($eid <= 1 || $lfid <= 1 || $ltid <= 1 || $lfid === $ltid) { return 409; }

                if (!$db->beginTransaction()) { throw new \RuntimeException('Movement transaction failed'); }
                $locked = self::lockMovementLocations($db, array($lfid, $ltid));
                if (!$locked) { return self::rejectMovement($db); }
                $from = $locked['locations'][$lfid];
                $to = $locked['locations'][$ltid];
                if ($from[MdLoc::$uuid] !== $fromUUID || $to[MdLoc::$uuid] !== $toUUID
                    || (int) $from[MdLoc::$typeId] === MdLocType::SERVICE
                    || ((int) $from[MdLoc::$typeId] === MdLocType::TRANSPORT && !$locked['properties'][$lfid])
                    || !MdLoc::isTeleportDestination($to, $locked['properties'][$ltid] ? 1 : 0)) {
                    return self::rejectMovement($db);
                }

                $equipmentRows = self::lockMovementRows($db, MdEq::$table, MdEq::$id, array($eid));
                $propertyRows = self::lockMovementRows($db, TEqProp::$table, TEqProp::$equipmentId, array($eid));
                $equipment = $equipmentRows[$eid] ?? false;
                $property = $propertyRows[$eid] ?? false;
                if (!$equipment || !$property || $equipment[MdEq::$uuid] !== $equipmentUUID
                    || (int) $equipment[MdEq::$isActive] !== 1 || (int) $property[TEqProp::$locId] !== $lfid
                    || (int) ($property[TEqProp::$serviceStatId] ?? 0) === MdEqServiceStat::IN_SERVICE) {
                    return self::rejectMovement($db);
                }

                self::writeEquipmentMovement($db, $eid, $actor[SyUser::$id], $lfid, $ltid, true);
                if (!$db->commit()) { throw new \RuntimeException('Movement commit failed'); }
                return 200;
            } catch (\Throwable $error) {
                return self::failMovement($db);
            }
        }

		public static function equipmentLocationChange($db, $eid, $uid, $lfid, $ltid, $operation){

            if (!(self::isMovementId($eid) && self::isMovementId($uid) && self::isMovementId($lfid) && self::isMovementId($ltid))) { return 409; }
            if ($db->inTransaction()) { return 500; }

			try {
                if (!$db->beginTransaction()) { throw new \RuntimeException('Movement transaction failed'); }
                $locked = self::lockMovementLocations($db, array($lfid, $ltid));
                if (!$locked) { return self::rejectMovement($db); }
                $equipmentRows = self::lockMovementRows($db, MdEq::$table, MdEq::$id, array($eid));
                $propertyRows = self::lockMovementRows($db, TEqProp::$table, TEqProp::$equipmentId, array($eid));
                if (!$equipmentRows || !$equipmentRows[$eid] || !$propertyRows || !$propertyRows[$eid]) {
                    return self::rejectMovement($db);
                }
                $equipment = $equipmentRows[$eid];
                $property = $propertyRows[$eid];
                $from = $locked['locations'][$lfid];
                $to = $locked['locations'][$ltid];
                $valid = $equipment[MdEq::$isActive] == 1 && $property[TEqProp::$locId] == $lfid && $lfid != $ltid
                    && (int) ($property[TEqProp::$serviceStatId] ?? 0) !== MdEqServiceStat::IN_SERVICE
                    && (int) $from[MdLoc::$typeId] !== MdLocType::SERVICE && (int) $to[MdLoc::$typeId] !== MdLocType::SERVICE;
                if ($operation === 'take') {
                    $transportProperty = $locked['properties'][$ltid];
                    $valid = $valid && self::equipmentTakeAllowed($from, $to, $transportProperty, $uid, $lfid);
                } elseif ($operation === 'leave') {
                    $transportProperty = $locked['properties'][$lfid];
                    $valid = $valid && $from[MdLoc::$typeId] == MdLocType::TRANSPORT && $from[MdLoc::$isActive] == 1
                        && $transportProperty && $transportProperty[TLocProp::$locStatId] == MdLocStat::T_TAKEN
                        && $transportProperty[TLocProp::$userId] == $uid && $transportProperty[TLocProp::$locIdPrnt] == $ltid;
                } else { $valid = false; }
                if (!$valid) { return self::rejectMovement($db); }


                self::writeEquipmentMovement($db, $eid, $uid, $lfid, $ltid);
                if (!$db->commit()) { throw new \RuntimeException('Movement commit failed'); }
                return 200;

            } catch (\Throwable $error) {
                return self::failMovement($db);
            }


		}

		public static function transportLocationChange($db, $lid, $uid, $lfid, $ltid, $status, $actor){

            if (!(self::isMovementId($lid) && self::isMovementId($uid) && self::isMovementId($lfid) && self::isMovementId($ltid))) { return 409; }
            if ($db->inTransaction()) { return 500; }

			try {
                if (!$db->beginTransaction()) { throw new \RuntimeException('Movement transaction failed'); }
                $locked = self::lockMovementLocations($db, array($lid, $lfid, $ltid));
                if (!$locked || !$locked['properties'][$lid]) { return self::rejectMovement($db); }
                $transport = $locked['locations'][$lid];
                $property = $locked['properties'][$lid];
                $to = $locked['locations'][$ltid];
                $valid = $transport[MdLoc::$typeId] == MdLocType::TRANSPORT && $transport[MdLoc::$isActive] == 1
                    && $property[TLocProp::$locStatId] == MdLocStat::T_TAKEN && $property[TLocProp::$locStatId] == $status
                    && $property[TLocProp::$locIdPrnt] == $lfid && $lfid != $ltid
                    && $to[MdLoc::$typeId] != MdLocType::TRANSPORT && self::movementOwnerAllowed($property, $actor);
                if (!$valid) { return self::rejectMovement($db); }
                $toService = (int) $to[MdLoc::$typeId] === MdLocType::SERVICE;
                if ($toService && (!$locked['properties'][$ltid]
                    || (!ReadData::serviceDropOffAllowed($to, $locked['properties'][$ltid])
                        && !ReadData::hasServiceEquipment($db, $ltid)))) { return self::rejectMovement($db); }
                if ($toService || (int) $locked['locations'][$lfid][MdLoc::$typeId] === MdLocType::SERVICE) {
                    self::requireInnoDB($db, array(MdLoc::$table, TLocProp::$table, TLocTrns::$table, TLocTrnsSub::$table));
                }

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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
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
				if (!$statement->execute()) { throw new \RuntimeException('Movement write failed'); }
                if ($statement->rowCount() !== 1) { throw new \RuntimeException('Movement property update failed'); }
				$statement->closeCursor();

                if (!$db->commit()) { throw new \RuntimeException('Movement commit failed'); }
                return 200;

            } catch (\Throwable $error) {
                return self::failMovement($db);
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

		
        // Authentication lookups must recheck the account's current enabled state.
        public static function getActiveSyUser($c, $i) {
            $qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
            $qry .= " AND " . self::$isActive . " = 1";
            $stmt = $c->prepare($qry);
            $stmt->bindParam(":id", $i, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

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
	

        public static function isTeleportDestination($location, $propertyCount) {
            $type = (int) $location[self::$typeId];
            return (int) $location[self::$id] > 1 && (int) $location[self::$isActive] === 1
                && $propertyCount <= 1
                && ($type === MdLocType::WAREHOUSE || $type === MdLocType::CONSTRUCTION_SITE
                    || ($type === MdLocType::TRANSPORT && $propertyCount === 1));
        }

		public static function getMdLoc($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getMdLocByUUID($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$uuid . " = :uuid";

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
        const SERVICE = 4;

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
        const SERVICE_OPEN = 6;
        const SERVICE_CLOSED = 7;


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
		public static $primaryTypeId = 'MD_EQ_TYPE_PRIMARY_ID';
		public static $secondaryTypeId = 'MD_EQ_TYPE_SECONDARY_ID';
	
        public static $warrantyDate = 'MD_EQ_WARRANTY_DATE';

		public static function getMdEq($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getMdEqByUUID($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$uuid . " = :id";
			
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

	class MdEqTypePrimary {

		public static $table       = 'md_eq_type_primary';
	
		public static $id          = 'MD_EQ_TYPE_PRIMARY_ID';
		public static $code        = 'MD_EQ_TYPE_PRIMARY_CODE';
		public static $name        = 'MD_EQ_TYPE_PRIMARY_NAME';
	
		public static function getMdEqTypePrimary($c, $i) {
			$qry = "SELECT * FROM " . self::$table . " WHERE " . self::$id . " = :id";
			
			$stmt = $c->prepare($qry);
			$stmt->bindParam(":id", $i, PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	
		public static function getAll($c) {
			$qry = "SELECT * FROM " . self::$table . " ORDER BY " . self::$id;

			$stmt = $c->prepare($qry);
			$stmt->execute();
	
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}


	class MdEqTypeSecondary {

		public static $table = 'md_eq_type_secondary';
		public static $id = 'MD_EQ_TYPE_SECONDARY_ID';
		public static $code = 'MD_EQ_TYPE_SECONDARY_CODE';
		public static $name = 'MD_EQ_TYPE_SECONDARY_NAME';
		public static $primaryTypeId = 'MD_EQ_TYPE_PRIMARY_ID';

		public static function matchesPrimary($db, $primaryTypeId, $secondaryTypeId) {
			$qry = "SELECT s.".self::$id." FROM ".self::$table." AS s "
				."JOIN ".MdEqTypePrimary::$table." AS p ON p.".MdEqTypePrimary::$id." = s.".self::$primaryTypeId
				." WHERE s.".self::$id." = :secondary_type AND p.".MdEqTypePrimary::$id." = :primary_type";
			$statement = $db->prepare($qry);
			$statement->bindValue(':primary_type', (int) $primaryTypeId, PDO::PARAM_INT);
			$statement->bindValue(':secondary_type', (int) $secondaryTypeId, PDO::PARAM_INT);
			$statement->execute();
			return $statement->fetch(PDO::FETCH_ASSOC) !== false;
		}

		public static function getByPrimaryType($db, $primaryTypeId) {
			$qry = "SELECT * FROM ".self::$table." WHERE ".self::$primaryTypeId." = :primary_type ORDER BY ".self::$id;
			$statement = $db->prepare($qry);
			$statement->bindValue(':primary_type', (int) $primaryTypeId, PDO::PARAM_INT);
			return $statement->execute() ? $statement->fetchAll(PDO::FETCH_ASSOC) : null;
		}
	}

	class MdEqServiceStat {

		const READY = 1;
		const NEEDS_SERVICE = 2;
		const IN_SERVICE = 3;
        const BROKEN = 4;

		public static $table = 'md_eq_service_stat';

		public static $id = 'MD_EQ_SERVICE_STAT_ID';
		public static $code = 'MD_EQ_SERVICE_STAT_CODE';
		public static $name = 'MD_EQ_SERVICE_STAT_NAME';

		public static function getMdEqServiceStat($c, $i) {
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
		public static $serviceStatId = 'MD_EQ_SERVICE_STAT_ID';
	
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
		public static $isTeleport = 'T_EQ_TRNS_ISTELEPORT';
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
	
    class TLocServiceEqTypes {
        public static $table = 't_loc_service_eq_types';
    }

    class TEqService {
        public static $table = 't_eq_service';
    }

    class TEqChecklist {
        public static $table = 't_eq_checklist';
        public static $id = 'T_EQ_CHECKLIST_ID';
        public static $assigneeId = 'SY_USER_ID_ASSIGNEE';
        public static $isClosed = 'T_EQ_CHECKLIST_ISCLOSED';
    }

    class TEqChecklistItem {
        public static $table = 't_eq_checklist_item';
    }

    class TEqChecklistItemTrns {
        public static $table = 't_eq_checklist_item_trns';
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