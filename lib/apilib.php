<?php
/* -----------------
 * PHP API
 * @author: Jani Pusenjak
 * @date: 26.10.2022
 * 
 * PHP classes definition file
 * 
 */
 
	namespace apilib;
	use apidb\Db;
    use apidb\SyUser;
use apidb\SyUserAcl;

    // Resolve runtime configuration beside this library, independently of the working directory.
    $configFile = __DIR__ . '/api.conf';
    $config = is_file($configFile) && is_readable($configFile)
        ? @parse_ini_file($configFile, true, INI_SCANNER_RAW) : false;
    define(__NAMESPACE__ . '\\CONFIG', is_array($config) ? $config : array());

    class Environment {
        public static function isProduction() {
            // Diagnostics require an explicit development profile; unknown/missing values stay disabled.
            return (CONFIG['environment'] ?? 'production') !== 'development';
        }
    }

    class AuthManager {
        // Public dummy hash: missing users still perform password verification.
        const DUMMY_PASSWORD_HASH = '$2y$12$u/sdERdSGpiCDs.VTe2NJO0S0Hl0fD7UrNK55nQGV54UmnVRoeWKu';


        const TOKEN_KY = "Authorization" ;

		public static function getAuthorizationHeader(){
			$headers = getallheaders();

			$token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
			return $token;
		}

        public static function getTokenFromHeader(){

            $apiKY = self::getAuthorizationHeader();

			return is_string($apiKY) ? str_replace('Bearer ', '', $apiKY) : null;

        } // getTokenFromHeader

		public static function getUserFromToken(){

			$apiKY = self::getAuthorizationHeader();

			if (!$apiKY) {
				return null;
			}

			$decoded = JWTManager::verifyJWT(str_replace('Bearer ', '', $apiKY));
            if (!is_array($decoded) || !APIMgmt::positiveId($decoded[APIMgmt::ID] ?? null)
                || !in_array($decoded[JWTManager::IS_REFRESH] ?? null, array(0, '0'), true)) { return null; }

			$db = Db::connectDB();
			$user = SyUser::getActiveSyUser($db, $decoded[APIMgmt::ID]);

			return $user;
		} // getUserFromHeader



    }  // AuthManager



	class AclManager {

		public static function managerSyUserChange($user, $data){
			
            if ($data === null || $data === array()) { return false; }
            if (!is_array($data)) { APIMgmt::badParamsResponse(); }
            $selected = $data[APIMgmt::AS_USER_KY] ?? null;
            if ($selected === null || $selected === '') { return false; }
            if (!APIMgmt::positiveId($selected)) { APIMgmt::badParamsResponse(); }

			if (!SyUserAcl::hasPrivilege($user, SyUserAcl::MANAGER)){
				return false;
			}

			if (array_key_exists(APIMgmt::AS_USER_KY, $data)){
				return $data[APIMgmt::AS_USER_KY];
			}

			return false;
		}


	} // AclManager



    class URIinx {
        const DOMAIN      = 0;   // segment 0
        const ROOT        = 1;   // segment 1
        const VERSION     = 2;   // segment 2
        const ENTITY   = 3;   // segment 3
        const ENDPOINT    = 4;   // segment 4
    } // URIinx




	class ReqURI {

        const URLMAP_CALL_KY = "call";
        const URLMAP_PROTECTED_KY = "protected";


        const INC_ROOT = "./inc" ;

        const EXT_FILENAME = ".php";

        const CALL_PING_FILENAME = "ping";
        const CALL_401_FILENAME = "err401";
        const CALL_403_FILENAME = "err403";
        const CALL_404_FILENAME = "err404";
        const CALL_500_FILENAME = "err500";



        public static $reqArray = array();
		protected static $paramArray = array();
		protected static $jsonBody = array();
        protected static $jsonBodyShape;


		public static $rootMap = array(
            self::URLMAP_CALL_KY=>'call.php'
            ,self::URLMAP_PROTECTED_KY=>'protected.php'

        );



		public static function readURI($url) {
            if (!is_string($url)) { APIMgmt::badParamsResponse(); }


			$prsu = parse_url($url) ;
            if (!is_array($prsu) || !isset($prsu['path']) || !is_string($prsu['path'])) { APIMgmt::badParamsResponse(); }
			$urlSeg = explode('/', $prsu['path']);

			self::setSegment($urlSeg, 0, URIinx::DOMAIN) ;
			self::setSegment($urlSeg, 1, URIinx::ROOT) ;
			self::setSegment($urlSeg, 2, URIinx::VERSION) ;
			self::setSegment($urlSeg, 3, URIinx::ENTITY) ;
			self::setSegment($urlSeg, 4, URIinx::ENDPOINT) ;

            // Standard form/query decoding, exactly once (including +, %, &, and =).
            self::setParams($prsu['query'] ?? '');

			// parse POST body
            $body = file_get_contents("php://input");
            self::$jsonBody = json_decode($body, true);
            self::$jsonBodyShape = json_decode($body);

		} // readURI



        private static function setSegment($seg, $inx, $segInx){

			if (isset($seg[$inx])) {
				self::$reqArray[$segInx] = $seg[$inx];
			}
			else {
				self::$reqArray[$segInx] = null ;
			}

		} // setSegment

		
        private static function setParams($query) {
            parse_str($query, $params);
            self::$paramArray = $params;
        } // setParams

        public static function isProtectedCall(){

			return self::$reqArray[URIinx::ROOT] == self::URLMAP_PROTECTED_KY ? true : false ;
			
		}


        public static function getParams(){
				return self::$paramArray;
		} // getParams

        public static function getJsonBody(){
            if (!is_array(self::$jsonBody) || array_is_list(self::$jsonBody)) { APIMgmt::badParamsResponse(); }
				return self::$jsonBody;
		} // getJsonBody


        public static function isJsonList($key) {
            // Associative decoding alone cannot distinguish [] from {} or {"0": ...}.
            if (is_object(self::$jsonBodyShape)) {
                return property_exists(self::$jsonBodyShape, $key) && is_array(self::$jsonBodyShape->{$key});
            }
            return isset(self::$jsonBody[$key]) && is_array(self::$jsonBody[$key]) && array_is_list(self::$jsonBody[$key]);
        }

        public static function isCallPing(){
			$test = false;

			if (self::$reqArray[URIinx::ROOT] == 'ping' ) {
				$test = true;
			}

			return $test;
			
		} // isCallPing




        public static function getPingFileName(){
			$file = self::INC_ROOT."/".self::CALL_PING_FILENAME.self::EXT_FILENAME;
			return $file;
		} // getPingFileName

        public static function get401FileName(){
			$file = self::INC_ROOT."/".self::CALL_401_FILENAME.self::EXT_FILENAME;
			return $file;
		} // get401FileName

        public static function get403FileName(){
			$file = self::INC_ROOT."/".self::CALL_403_FILENAME.self::EXT_FILENAME;
			return $file;
		} // get403FileName

        public static function get404FileName(){
			$file = self::INC_ROOT."/".self::CALL_404_FILENAME.self::EXT_FILENAME;
			return $file;
		} // get404FileName

		public static function get500FileName(){
			$file = self::INC_ROOT."/".self::CALL_500_FILENAME.self::EXT_FILENAME;
			return $file;
		} // get500FileName		




        public static function isFileName(){

			//$test = is_readable( self::getCallFileName() );
			$test = file_exists( self::getFileName() );

			return $test;
			
		} // isCallFileName


        public static function isPageFileName(){

			$test = false;

			if (self::$reqArray[URIinx::ENTITY] != null) {
				$test = true;
			}

			return $test;
			
		} // isPageFileName
        


        public static function getFileName(){
			
			$file = self::INC_ROOT;
			
			$file .= isset(self::$reqArray[URIinx::ROOT])      ? "/".self::$reqArray[URIinx::ROOT]     : "" ;
			$file .= isset(self::$reqArray[URIinx::VERSION])   ? "/".self::$reqArray[URIinx::VERSION]  : "" ;
			$file .= isset(self::$reqArray[URIinx::ENTITY])       ? "/".self::$reqArray[URIinx::ENTITY]      : "" ;
			$file .= isset(self::$reqArray[URIinx::ENDPOINT])  ? "/".self::$reqArray[URIinx::ENDPOINT] : "" ;

			$file .= self::EXT_FILENAME;
			return $file;
			
		} // getCallFileName
        


        public static function getPageFileName(){
			
			$file = self::INC_ROOT;
			
			$file .= isset(self::$reqArray[URIinx::ROOT])    ? "/".self::$reqArray[URIinx::ROOT]    : "" ;
			$file .= isset(self::$reqArray[URIinx::ENTITY]) ? "/".self::$reqArray[URIinx::ENTITY] : "" ;

			$file .= self::EXT_FILENAME;
			return $file;
			
		} // getPageFileName



	} // ReqURI






    class RateLimiter {
        const RPM = 60;
        const AUTH_RPM = 10;
        const TIME_WINDOW_SEC = 60;
        // Retained for existing callers; the active policy permits bursts within minute budgets.
        const MAX_RATE_SPEED_SEC = 1;
        private static $retryAfter = 0;

        public static function canRequest($apyky, $limit = self::RPM) {
            $now = microtime(true);
            if (!isset($_SESSION[SessionMgmt::CACHE_KY]) || !is_array($_SESSION[SessionMgmt::CACHE_KY])) { SessionMgmt::clearCache(); }
            $cache = SessionMgmt::getCache($apyky);
            $cache = is_array($cache) ? $cache : array();
            $cache = array_values(array_filter($cache, function($stamp) use ($now) {
                return (is_int($stamp) || is_float($stamp)) && $stamp > $now - self::TIME_WINDOW_SEC;
            }));
            sort($cache, SORT_NUMERIC);
            SessionMgmt::setCache($cache, $apyky);
            self::$retryAfter = 0;
            if (count($cache) >= $limit) {
                self::$retryAfter = max(1, (int) ceil($cache[0] + self::TIME_WINDOW_SEC - $now));
                return false;
            }
            SessionMgmt::addCache($now, $apyky);
            return true;
        }

        public static function getRetryAfter() { return self::$retryAfter; }

        private static function rejectRequest() {
            $retryAfter = self::$retryAfter;
            if (!session_write_close()) { APIMgmt::sendResponse(1, 'Service unavailable', 503); }
            header('Retry-After: ' . $retryAfter);
            APIMgmt::sendResponse(1, 'Too many requests', 429);
        }

        public static function enforceRequest() {
            if (session_status() !== PHP_SESSION_ACTIVE) { APIMgmt::sendResponse(1, 'Service unavailable', 503); }
            if (!self::canRequest('api')) { self::rejectRequest(); }
            $route = ReqURI::$reqArray;
            if (($route[URIinx::ROOT] ?? null) === ReqURI::URLMAP_CALL_KY && ($route[URIinx::ENTITY] ?? null) === 'auth') {
                if (!self::canRequest('auth', self::AUTH_RPM)) { self::rejectRequest(); }
            }
            // Persist counters atomically, then release the session lock before DB/network work.
            if (!session_write_close()) { APIMgmt::sendResponse(1, 'Service unavailable', 503); }
        }
    } // RateLimiter


    class SessionMgmt {
		

		const CACHE_KY = "APICACHE";   // KY cache register


		public static $timeout = 3600;		

		
		
		public static function destroy(){
			
			$_SESSION = array();
			session_destroy();
			
		} // destroy		
		
		
		
		// CACHE management
		public static function clearCache(){
			$cache = array();
			$_SESSION[self::CACHE_KY] = $cache;
		} // clearCache		
	


		public static function isCacheSet($cacheInx){
			$test = false ;

			if (isset($_SESSION[self::CACHE_KY])) {
				$cache = $_SESSION[self::CACHE_KY]  ;
				$test = isset( $cache[$cacheInx] ) ;
			}
            else { // init session cache
                $_SESSION[self::CACHE_KY] = array();
            }

			return $test;
		} // isCacheSet	
	

		public static function setCache($c, $cacheInx){		
			$_SESSION[self::CACHE_KY][$cacheInx] = $c;
		} // setCache

        public static function initCache($cacheInx){		
			$_SESSION[self::CACHE_KY][$cacheInx] = array();
		} // setCache


        public static function addCache($c, $cacheInx){		
                array_push($_SESSION[self::CACHE_KY][$cacheInx], $c);
		} // setCache

		public static function getCache($cacheInx){

			$cache = null ;
			if (self::isCacheSet($cacheInx)) {
				$cache = $_SESSION[self::CACHE_KY][$cacheInx];
			} // if

			return $cache ;
		} // getCache

	}  // SessionMgmt	





    // TODO: remove this
    class TestLogger {
        const C_KY = "TEST_LOGGER";


        public static function init(){
            SessionMgmt::initCache(self::C_KY);
        }

        public static function log($v){
            if (!SessionMgmt::isCacheSet(self::C_KY)){
                self::init();
            }
            SessionMgmt::addCache($v, self::C_KY);
        }

        public static function clear(){
            SessionMgmt::setCache(array(), self::C_KY);
        }

    }




    class APIMgmt {

		// in keys
        const ERR_KY      = "error";
        const RC_KY      = "RC";
        const MSG_KY     = "message";

        const DATA_KY     = "data";
        const AC_TKN_KY     = "token";
        const RE_TNK_KY     = "refresh_token";

		const FILTER_KY	 = "fltr";
		const RPP_KY	 = "rpp";
		const PAGE_KY	 = "pge";

        const PAGINATION_MAX_RPP = 1000;
        const PAGINATION_MAX_PAGE = 1000000;
		const SORT_KY	 = "srt";

        const PWD_KY     = "gsl";
        const USR_KY     = "ime";
        const TOKEN_KY     = "token";

		const USER_KY = 'user';
		const ELEM_KY = 'element';
		const ELEM_LIST_KY = 'element_list';
		const ID_KY = 'id';
		const TYPE_KY = 'type';
		const DATE_FROM_KY = 'date_from';
		const DATE_TO_KY = 'date_to';
		
		const PRNT_FROM_KY = 'parent_from';
		const PRNT_TO_KY = 'parent_to';
		const PRNT_FROM_NAME_KY = 'parent_from_name';
		const PRNT_TO_NAME_KY = 'parent_to_name';
		const PRNT_FROM_TYPE_KY = 'parent_from_type';
		const PRNT_TO_TYPE_KY = 'parent_to_type';

		const AS_USER_KY = 'as_user';


		// out keys
		const ID = 'id';
		const NAME = 'name';
		const SURNAME = 'surname';
		const ACL = 'acl';
		const EMAIL = 'email';
		const CODE = 'code';
		const DESC = 'desc';
		const UUID = 'uuid';
		const ISACTIVE = 'isActive';
		const TIME = 'time';
		const ISLAST = 'isLast';
		const TYPE_ID = 'typeId';
		const STATUS_ID = 'statusId';
		const SERVICE_STATUS_ID = 'serviceStatusId';
		const PRNT = 'parent';
		const PRNT_ID = 'parentId';
		const PRNT_NAME = 'parentName';
		const PRNT_UUID = 'parentUuid';
		const IS_WRH = 'isWarehouse';
		const COUNT_CHILD = 'childCount';
		const SELECTED = 'selected';

		const PRNT_FROM = 'parentFrom';
		const PRNT_TO = 'parentTo';
		const PRNT_FROM_NAME = 'parentFromName';
		const PRNT_TO_NAME = 'parentToName';
		const PRNT_FROM_TYPE = 'parentFromType';
		const PRNT_TO_TYPE = 'parentToType';

		public static function badParamsResponse(){
			error_log("BAD PARAMS");
			http_response_code(400);
			$data = array();
			$data[APIMgmt::ERR_KY] = 1;
			$data[APIMgmt::MSG_KY] = "Bad request";
			echo json_encode($data);
			exit();
		}

		public static function serverErrorResponse(){
			http_response_code(500);
			$data = array();
			$data[APIMgmt::ERR_KY] = 1;
			$data[APIMgmt::MSG_KY] = "Server error";
			echo json_encode($data);
			exit();
		}

		public static function sendResponse($err, $message, $status){

				http_response_code($status);
				$data = array();
				$data[APIMgmt::ERR_KY] = $err;
				$data[APIMgmt::MSG_KY] = $message;
				echo json_encode($data);
				exit();
		}

		
		public static function checkMethod($allowed){
			$method = $_SERVER['REQUEST_METHOD'];

			if (!in_array($method, $allowed)) {
				header("HTTP/1.1 405 Method Not Allowed");
				header("Allow: " . implode(", ", $allowed));
				echo json_encode([APIMgmt::ERR_KY => "Method Not Allowed"]);
				exit();
			}

			return $method;
		}
		
        public static function getPagination($params) {
            if (!is_array($params)) { self::badParamsResponse(); }
            $values = array();
            foreach (array(self::RPP_KY => self::PAGINATION_MAX_RPP, self::PAGE_KY => self::PAGINATION_MAX_PAGE) as $key => $maximum) {
                $value = $params[$key] ?? null;
                if ((!is_int($value) && !is_string($value))
                    || !preg_match('/^[1-9][0-9]*$/D', (string) $value)) { self::badParamsResponse(); }
                $value = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => $maximum)));
                if ($value === false) { self::badParamsResponse(); }
                $values[$key] = $value;
            }
            return array(($values[self::PAGE_KY] - 1) * $values[self::RPP_KY], $values[self::RPP_KY]);
        }

        public static function nonEmptyString($value) {
            return is_string($value) && trim($value) !== '';
        }

        public static function positiveId($value) {
            return (is_int($value) || is_string($value))
                && filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) !== false;
        }

        public static function checkStringParams($params, $keys) {
            self::checkRequiredParams($params, $keys);
            foreach ($keys as $key) {
                if (!self::nonEmptyString($params[$key])) { self::badParamsResponse(); }
            }
        }

        public static function optionalString($params, $key) {
            $value = $params[$key] ?? null;
            if ($value !== null && !is_string($value)) { self::badParamsResponse(); }
            return $value;
        }

        public static function sortKey($params) {
            $value = $params[self::SORT_KY] ?? 1;
            if ((!is_int($value) && !is_string($value)) || !in_array($value, array(1, 2, 3, '1', '2', '3'), true)) {
                self::badParamsResponse();
            }
            return (int) $value;
        }

        public static function requireRecord($record, $status = 404) {
            if (!is_array($record) || !$record) {
                self::sendResponse(1, $status === 404 ? 'Record not found' : 'Incomplete record', $status);
            }
        }

        public static function checkParams($params, $expected) {
            if ($params === null) { $params = array(); }
            if (!is_array($params) || ($params && array_is_list($params))) { self::badParamsResponse(); }
            foreach ($expected as $key) { $params[$key] = $params[$key] ?? null; }
            return $params;
        }

        public static function checkRequiredParams($params, $expected) {
            if (!is_array($params) || array_is_list($params)) { self::badParamsResponse(); }
            foreach ($expected as $key) {
                if (!array_key_exists($key, $params)) { self::badParamsResponse(); }
            }
        }


    } // APIMgmt


	class JWTManager {

		public const EXPIRE_MAP = array(
			"access" => 900,  // 15 minutes
			"refresh" => 1209600 // 14 days
		);

		public const ACCESS_EXPIRY = "access";
		public const REFRESH_EXPIRY = "refresh";

		public const IS_REFRESH = "is_refresh";

		protected const JWT_CNF_FILE = "jwt.conf";
		protected static $secret;

		private static function getSecret(){
			if (!isset(self::$secret)){
				$config = parse_ini_file(self::JWT_CNF_FILE, true);
				self::$secret = $config['secret'];
			}
			return self::$secret;
		}


		public static function base64UrlEncode($data) {
			return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
		}
		
		public static function base64UrlDecode($data) {
			return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
		}
		
		public static function createJWT($payload, $expiry = self::ACCESS_EXPIRY) {
			$expiryTime = self::EXPIRE_MAP[$expiry];
			$header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
			$payload['exp'] = time() + $expiryTime; // Access token expires in 15 minutes
			$payloadEncoded = self::base64UrlEncode(json_encode($payload));
		
			$signature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payloadEncoded", self::getSecret(), true));
			return "$header.$payloadEncoded.$signature";
		}
		
		public static function verifyJWT($token) {
            if (!is_string($token)) { return null; }
			$parts = explode('.', $token);
			if (count($parts) !== 3) return null;

			list($header, $payload, $signature) = $parts;
			$decodedPayload = json_decode(self::base64UrlDecode($payload), true);
            if (!is_array($decodedPayload) || array_is_list($decodedPayload)
                || !isset($decodedPayload['exp']) || !is_int($decodedPayload['exp'])) { return null; }
			
			if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) return null;
		
			$expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", self::getSecret(), true));
			return hash_equals($expectedSignature, $signature) ? $decodedPayload : null;
		}


	}


?>