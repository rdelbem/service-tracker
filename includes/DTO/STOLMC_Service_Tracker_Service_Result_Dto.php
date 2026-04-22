<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Service Result DTO for uniform internal service contract.
 *
 * This DTO provides a consistent way for services to return results
 * that can be mapped to appropriate REST API responses.
 */
class STOLMC_Service_Tracker_Service_Result_Dto {

	/**
	 * Whether the operation was successful.
	 *
	 * @var bool
	 */
	public bool $success;

	/**
	 * Result data (if successful) or additional error data.
	 *
	 * @var mixed|null
	 */
	public mixed $data;

	/**
	 * Error code for failures.
	 *
	 * @var string|null
	 */
	public ?string $error_code;

	/**
	 * Human-readable message.
	 *
	 * @var string|null
	 */
	public ?string $message;

	/**
	 * HTTP status code for the response.
	 *
	 * @var int
	 */
	public int $http_status;

	/**
	 * Constructor.
	 *
	 * @param bool        $success     Whether the operation was successful.
	 * @param mixed       $data        Result data (if successful) or additional error data.
	 * @param string|null $error_code  Error code for failures.
	 * @param string|null $message     Human-readable message.
	 * @param int         $http_status HTTP status code for the response.
	 */
	public function __construct(
		bool $success,
		mixed $data = null,
		?string $error_code = null,
		?string $message = null,
		int $http_status = 200
	) {
		$this->success     = $success;
		$this->data        = $data;
		$this->error_code  = $error_code;
		$this->message     = $message;
		$this->http_status = $http_status;
	}

	/**
	 * Create a successful result.
	 *
	 * @param mixed $data        Result data.
	 * @param int   $http_status HTTP status code.
	 *
	 * @return self
	 */
	public static function ok( mixed $data = null, int $http_status = 200 ): self {
		return new self( true, $data, null, null, $http_status );
	}

	/**
	 * Create a failure result.
	 *
	 * @param string $error_code  Error code.
	 * @param string $message     Human-readable message.
	 * @param int    $http_status HTTP status code.
	 * @param mixed  $data        Additional error data.
	 *
	 * @return self
	 */
	public static function fail(
		string $error_code,
		?string $message = null,
		int $http_status = 400,
		mixed $data = null
	): self {
		return new self( false, $data, $error_code, $message, $http_status );
	}

	/**
	 * Check if the result is successful.
	 *
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * Check if the result is a failure.
	 *
	 * @return bool
	 */
	public function is_failure(): bool {
		return ! $this->success;
	}

	/**
	 * Get the HTTP status code.
	 *
	 * @return int
	 */
	public function get_http_status(): int {
		return $this->http_status;
	}

	/**
	 * Get the error code.
	 *
	 * @return string|null
	 */
	public function get_error_code(): ?string {
		return $this->error_code;
	}

	/**
	 * Get the message.
	 *
	 * @return string|null
	 */
	public function get_message(): ?string {
		return $this->message;
	}

	/**
	 * Get the data.
	 *
	 * @return mixed
	 */
	public function get_data(): mixed {
		return $this->data;
	}
}
