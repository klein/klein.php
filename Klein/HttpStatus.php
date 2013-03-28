<?php
/**
 * Klein (klein.php) - A lightning fast router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/chriso/klein.php
 * @license     MIT
 */

namespace Klein;

/**
 * HttpStatus 
 *
 * HTTP status code and message translator
 * 
 * @package		Klein
 */
class HttpStatus {

	/**
	 * The HTTP status code
	 * 
	 * @var int
	 * @access protected
	 */
	protected $code;

	/**
	 * The HTTP status message
	 * 
	 * @var string
	 * @access protected
	 */
	protected $message;

	/**
	 * HTTP 1.1 status messages based on code
	 *
	 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 * @var array
	 * @access protected
	 * @static
	 */
	protected static $http_message = array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',
		// Successful 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);


	/**
	 * Constructor
	 * 
	 * @param int $code The HTTP code
	 * @param string $message (optional) HTTP message for the corresponding code
	 * @access public
	 * @return void
	 */
	public function __construct( $code, $message = null ) {
		$this->set_code( $code );

		if ( is_null( $message ) ) {
			$message = static::get_message_from_code( $code );
		}

		$this->message = $message;
	}

	/**
	 * @access public
	 * @return int
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @param int $code 
	 * @access public
	 * @return HttpStatus
	 */
	public function set_code( $code ) {
		$this->code = (int) $code;
		return $this;
	}

	/**
	 * @param string $message 
	 * @access public
	 * @return HttpStatus
	 */
	public function set_message( $message ) {
		$this->message = (string) $message;
		return $this;
	}

	/**
	 * Get a string representation of our HTTP status
	 * 
	 * @access public
	 * @return string
	 */
	public function get_formatted_string() {
		$string = (string) $this->code;

		if ( !is_null( $this->message ) ) {
			$string = $string . ' ' . $this->message;
		}

		return $string;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->get_formatted_string();
	}

	/**
	 * Get our HTTP 1.1 message from our passed code
	 *
	 * Returns null if no corresponding message was
	 * found for the passed in code
	 * 
	 * @param int $int 
	 * @static
	 * @access public
	 * @return string | null
	 */
	public static function get_message_from_code( $int) {
		if ( isset( static::$http_message[ $int ] ) ) {
			return static::$http_message[ $int ];
		}
		else {
			return null;
		}
	}

} // End class HttpStatus
