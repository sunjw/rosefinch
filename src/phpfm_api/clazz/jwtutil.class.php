<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT Utility Class
 * 2022-04-19
 * @author Sun Junwen
 *
 */
class JwtUtil {

    private static $JWT_ALG = 'HS256';
    private static $JWT_EXP = 'exp';
    private static $JWT_TIMEOUT = 3600; // 3600s

    /**
     * Encode JWT with payload.
     * @param $payload
     * @return string
     */
    public static function encode($payload, $temp_jwt_key = null) {
        $payload[self::$JWT_EXP] = time() + self::$JWT_TIMEOUT;
        $use_jwt_key = JWT_KEY;
        if (!is_null($temp_jwt_key)) {
            $use_jwt_key = $temp_jwt_key;
        }
        $jwt = JWT::encode($payload, $use_jwt_key, self::$JWT_ALG);
        return $jwt;
    }

    /**
     * Decode JWT to payload.
     * @param $jwt
     * @return array|null
     */
    public static function decode($jwt) {
        $payload_array = null;
        try {
            $payload = JWT::decode($jwt, new Key(JWT_KEY, self::$JWT_ALG));
            $payload_array = (array)$payload;
        } catch (Exception $e) {
        }
        return $payload_array;
    }

    /**
     * Update JWT exp if needed.
     * @param $jwt
     * @return false|string
     */
    public static function update_exp($jwt) {
        $payload = self::decode($jwt);
        if ($payload == null) {
            return false;
        }
        $exp_time = $payload[self::$JWT_EXP];
        $cur_time = time();
        if (($exp_time - $cur_time) < (self::$JWT_TIMEOUT / 2)) {
            return self::encode($payload);
        }
        return false;
    }
}

?>
