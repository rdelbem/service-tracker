<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Users_Query_Dto {
	public int $page;
	public int $per_page;
	public string $query;

	public function __construct( int $page = 1, int $per_page = 6, string $query = '' ) {
		$this->page     = max( 1, $page );
		$this->per_page = max( 1, $per_page );
		$this->query    = trim( $query );
	}
}
