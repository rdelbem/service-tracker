<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Progress_Upload_Request_Dto {
	/**
	 * @var array<string, mixed>
	 */
	public array $files;

	/**
	 * @var array<string, mixed>
	 */
	public array $body;

	/**
	 * @param array<string, mixed> $files Uploaded files payload.
	 * @param array<string, mixed> $body Request body payload.
	 */
	public function __construct( array $files, array $body ) {
		$this->files = $files;
		$this->body  = $body;
	}
}
