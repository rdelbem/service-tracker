<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Cases_Search_Query_Dto {
	public string $query;
	public int $user_id;
	public int $page;
	public int $per_page;

	public function __construct( string $query = '', int $user_id = 0, int $page = 1, int $per_page = 6 ) {
		$this->query    = trim( $query );
		$this->user_id  = max( 0, $user_id );
		$this->page     = max( 1, $page );
		$this->per_page = max( 1, $per_page );
	}
}
