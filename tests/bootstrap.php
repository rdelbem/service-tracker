<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up BrainMonkey and loads the plugin files for testing.
 *
 * @package Service_Tracker
 */

// Load Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

// Load test helper functions.
require_once __DIR__ . '/helpers.php';

// Load base test case classes explicitly.
// This avoids reliance on autoload-dev resolution order in different environments.
require_once __DIR__ . '/Unit/Unit_TestCase.php';
require_once __DIR__ . '/Unit/API_TestCase.php';

// Define constants for testing.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'STOLMC_SERVICE_TRACKER_VERSION' ) ) {
	define( 'STOLMC_SERVICE_TRACKER_VERSION', '1.0.0' );
}

// Mock WordPress classes for unit testing.
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $code;
		public $message;
		public $data = [];

		public function __construct( $code = '', $message = '', $data = [] ) {
			$this->code = $code;
			$this->message = $message;
			$this->data = $data;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_code() {
			return $this->code;
		}
	}
}

if ( ! class_exists( 'WP_User' ) ) {
	class WP_User {
		public $ID;
		public $roles = [];
		public $caps = [];

		public function __construct( $id = 0 ) {
			$this->ID = $id;
			$this->roles = [];
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		private $data;
		private $status;
		private $headers = [];
		
		public function __construct( $data = null, $status = 200, $headers = [] ) {
			$this->data = $data;
			$this->status = $status;
			$this->headers = $headers;
		}
		
		public function get_data() {
			return $this->data;
		}
		
		public function get_status() {
			return $this->status;
		}
		
		public function get_headers() {
			return $this->headers;
		}
		
		public function set_status( $status ) {
			$this->status = $status;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	// Mock REST request class with ArrayAccess support.
	class WP_REST_Request implements \ArrayAccess {
		private $params = [];
		private $body = '';
		private $headers = [];
		
		public function __construct( $params = [], $body = '', $headers = [] ) {
			$this->params = $params;
			$this->body = $body;
			$this->headers = $headers;
		}
		
		public function get_param( $key ) { 
			return $this->params[ $key ] ?? null; 
		}
		
		public function get_body() { 
			return $this->body; 
		}
		
		public function get_json_params() { 
			return json_decode( $this->body, true ); 
		}
		
		public function get_headers() { 
			return $this->headers; 
		}
		
		// ArrayAccess implementation.
		public function offsetExists( $offset ): bool {
			return isset( $this->params[ $offset ] );
		}
		
		public function offsetGet( $offset ): mixed {
			return $this->params[ $offset ] ?? null;
		}
		
		public function offsetSet( $offset, $value ): void {
			$this->params[ $offset ] = $value;
		}
		
		public function offsetUnset( $offset ): void {
			unset( $this->params[ $offset ] );
		}
	}
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
	class WP_REST_Server {
		const READABLE = 'GET';
		const EDITABLE = 'POST, PUT, PATCH';
		const DELETABLE = 'DELETE';
		const CREATABLE = 'POST';
		const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
	}
}
