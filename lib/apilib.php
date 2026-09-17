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

    class AuthManager {


        const TOKEN_KY = "Authorization" ;

		public static function getAuthorizationHeader(){
			$headers = getallheaders();

			$token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
			return $token;
		}

        public static function getTokenFromHeader(){

            $apiKY = self::getAuthorizationHeader();

			return is_null($apiKY) ? null : str_replace('Bearer ', '', $apiKY);

        } // getTokenFromHeader

		public static function getUserFromToken(){

			$apiKY = self::getAuthorizationHeader();

			if (!$apiKY) {
				return null;
			}

			$decoded = JWTManager::verifyJWT(str_replace('Bearer ', '', $apiKY));

			$db = Db::connectDB();
			$user = SyUser::getSyUser($db, $decoded[APIMgmt::ID]);

			return $user;
		} // getUserFromHeader



    }  // AuthManager



	class AclManager {

		public static function managerSyUserChange($user, $data){
			
			if(!$data){
				return false;
			}

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


		public static $rootMap = array(
            self::URLMAP_CALL_KY=>'call.php'
            ,self::URLMAP_PROTECTED_KY=>'protected.php'

        );



		public static function readURI($url) {

			error_log("API called: ".$url);

			$prsu = parse_url($url) ;
			$urlSeg = explode('/', $prsu['path']);

			self::setSegment($urlSeg, 0, URIinx::DOMAIN) ;
			self::setSegment($urlSeg, 1, URIinx::ROOT) ;
			self::setSegment($urlSeg, 2, URIinx::VERSION) ;
			self::setSegment($urlSeg, 3, URIinx::ENTITY) ;
			self::setSegment($urlSeg, 4, URIinx::ENDPOINT) ;

			// parse GET params
			if (isset($prsu['query'])) {
				$urlParam = explode('&',$prsu['query']);
				self::setParams($urlParam);
			}
			else {
				self::$paramArray = null;
			}

			// parse POST body
			self::$jsonBody = json_decode(file_get_contents("php://input"), true);

		} // readURI



        private static function setSegment($seg, $inx, $segInx){

			if (isset($seg[$inx])) {
				self::$reqArray[$segInx] = $seg[$inx];
			}
			else {
				self::$reqArray[$segInx] = null ;
			}

		} // setSegment

		
        private static function setParams($seg){

			foreach ($seg as $curr){
				if($curr == ""){
					continue;
				}
				
				$currPair = explode('=', $curr);
				self::$paramArray[$currPair[0]] = $currPair[1];
			}

		} // setParams

        public static function isProtectedCall(){

			return self::$reqArray[URIinx::ROOT] == self::URLMAP_PROTECTED_KY ? true : false ;
			
		}


        public static function getParams(){
				return self::$paramArray;
		} // getParams

        public static function getJsonBody(){
				return self::$jsonBody;
		} // getJsonBody


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
        const MAX_RATE_SPEED_SEC = 1;
        const TIME_WINDOW_SEC = 60;


        public static function canRequest($apyky) {

            $out = true;

            TestLogger::clear();
            TestLogger::log(time());

            // if cache is not set init cache and add request to cache
            if (!SessionMgmt::isCacheSet($apyky)){
                
                SessionMgmt::initCache($apyky);
                SessionMgmt::addCache(time(), $apyky);

            }  
            else {
                // clear expired requests cache if needed
                if (count(SessionMgmt::getCache($apyky)) >= self::RPM){
                    self::clearExpiredCache($apyky);
                } 
                // check if last request was more than a second ago
                $check = self::checkTimeSinceLastRequest($apyky);

                if (count(SessionMgmt::getCache($apyky)) >= self::RPM || !$check) {
                    $out = false;
                }

                else {
                    SessionMgmt::addCache(time(), $apyky);
                }
                


            }

            return $out;


        } // canRequest

        private static function clearExpiredCache($apyky){
            $expired = array();
            foreach ( SessionMgmt::getCache($apyky) as $curr ){
                // if cache is older than the time window
                if (time() >= $curr + self::TIME_WINDOW_SEC){
                    array_push($expired, $curr);
                }
            }

            $newCache = array_values(array_diff(SessionMgmt::getCache($apyky), $expired));

            SessionMgmt::setCache($newCache, $apyky);
        } // clearExpiredCache

        private static function checkTimeSinceLastRequest($apyky){
            $cache = SessionMgmt::getCache($apyky);
            $out = true;

            if (count($cache) > 0){
                $lastTime = $cache[count($cache)-1];
                $out = time()-$lastTime > self::MAX_RATE_SPEED_SEC-1;
            }

            return $out;

        } // checkTimeSinceLastRequest



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
		
		public static function checkParams($params, $expected){

			if (isset($params)) {
				foreach ($expected as $curr) {
					$params[$curr] = isset($params[$curr]) ? $params[$curr] : null;
				}
			}
			return $params;
		}
		
		public static function checkRequiredParams($params, $expected){
			$out = isset($params);

			if (isset($params)) {
				foreach ($expected as $curr) {
					if (!array_key_exists($curr, $params)) {
						$out = false;
					}
				}
			}

			if (!$out) {
				APIMgmt::badParamsResponse();
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
			$parts = explode('.', $token);
			if (count($parts) !== 3) return null;

			list($header, $payload, $signature) = $parts;
			$decodedPayload = json_decode(self::base64UrlDecode($payload), true);
			
			if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) return null;
		
			$expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", self::getSecret(), true));
			return hash_equals($expectedSignature, $signature) ? $decodedPayload : null;
		}


	}


?>