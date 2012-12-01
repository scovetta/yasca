<?
declare(encoding='UTF-8');
namespace Yasca\Core;

/**
 * Wraps and extends functionality around character encodings.
 * @author Cory Carson <cory.carson@boeing.com> (version 3)
 */
final class Encoding {
	private function __construct(){}

	/**
	 * https://wiki.php.net/rfc/class_name_scalars
	 */
	const _class = __CLASS__;

	private static $detectOrder = ['UTF-8', 'windows-1251', 'ISO-8859-1',];

	/**
	 * Detects the encoding of the provided string.
	 * Extends mb_detect_encoding to detect UTF-16 and UTF-32,
	 * as well as throwing an exception when detection fails.
	 * @param string $str
	 * @throws EncodingException
	 * @return string The encoding of the provided string
	 */
	public static function detect($str){
		$encoding = \mb_detect_encoding(
			$str,
			self::$detectOrder,
			true
		);
		if ($encoding !== false){
			return $encoding;
		}

		//As of PHP 5.4.8, UTF-16 encoding detection fails always
		//http://us.php.net/manual/en/function.mb-detect-encoding.php
		$first4 = \substr($retval, 0, 4);

		if 		 ($first4 === "\x00\x00\xFE\xFF"){
			return 'UTF-32BE';
		} elseif ($first4 === "\xFE\xFF\x00\x00"){
			return 'UTF-32LE';
		}

		$first2 = \substr($retval, 0, 2);
		if ($first2 === "\xFE\xFF"){
			return 'UTF-16BE';
		} elseif ($first2 === "\xFF\xFE"){
			return 'UTF-16LE';
		} else {
			throw new EncodingException('Unable to detect encoding');
		}
	}

	/**
	 * Converts the string to the target encoding
	 * @param string $str
	 * @param string $targetEncoding
	 * @throws EncodingException
	 * @return string
	 */
	public static function convert($str, $targetEncoding = 'UTF-8'){
		return (new \Yasca\Core\FunctionPipe)
		->wrap($str)
		->pipe([self::_class, 'detect'])
		->pipeLast('\mb_convert_encoding', $str, $targetEncoding)
		->unwrap();
	}

	/**
	 * Returns file contents, converted to the target encoding.
	 * @param string $filepath
	 * @param string $targetEncoding
	 * @return string
	 */
	public static function getFileContentsAsString($filepath, $targetEncoding = 'UTF-8'){
		return (new \Yasca\Core\FunctionPipe)
		->wrap($filepath)
		->pipe('\file_get_contents', false)
		->pipe([self::_class, 'convert'], $targetEncoding)
		->unwrap();
	}

	/**
	 * Returns array of UTF-8 strings
	 * @param string $filepath
	 * @return array of string
	 */
	public static function getFileContentsAsArray($filepath){
		return \preg_split('`(*ANY)\R`u', self::getFileContentsAsString($filepath));
	}
}