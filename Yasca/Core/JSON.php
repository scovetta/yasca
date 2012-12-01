<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Wrap the json_* functions to throw an Exception on failure
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class JSON {
	private function __construct(){}

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	/**
	 * Returns the JSON representation of $value
	 * See http://php.net/manual/en/function.json-encode.php
	 * @param mixed $value The value being encoded
	 * @param int $options Bitmask of JSON_ constants
	 * @throws Exception
	 * @return string
	 */
	public static function encode($value, $options = 0){
		$retval = \json_encode($value, $options);
		self::throwIfNecessary();
		return $retval;
	}

	/**
	 * Returns the JSON representation of $value
	 * See http://php.net/manual/en/function.json-encode.php
	 * @param string $json The UTF-8 encoded JSON string
	 * @param bool $assoc When true, objects are converted into associative arrays
	 * @param int $depth Maximum stack depth
	 * @param int $options Bitwise mask of JSON_* constants
	 * @throws Exception
	 * @return object|array
	 */
	public static function decode($json, $assoc = false, $depth = 512, $options = 0){
		$retval = \json_decode($json, $assoc, $depth, $options);
		self::throwIfNecessary();
		return $retval;
	}

	private static function throwIfNecessary(){
		$err = \json_last_error();
		if 		 ($err === JSON_ERROR_NONE){
			return;
		} elseif ($err === JSON_ERROR_DEPTH){
			throw new JSONException('JSON value exceeded maximum stack depth');
		} elseif ($err === JSON_ERROR_CTRL_CHAR){
			throw new JSONException('JSON value contains invalid control character');
		} elseif ($err === JSON_ERROR_STATE_MISMATCH){
			throw new JSONException('JSON value is invalid or malformed');
		} elseif ($err === JSON_ERROR_SYNTAX){
			throw new JSONException('JSON syntax error');
		} elseif ($err === JSON_ERROR_UTF8){
			throw new JSONException('JSON invalid UTF-8 characters');
		} else {
			throw new JSONException('JSON unknown error');
		}
	}
}