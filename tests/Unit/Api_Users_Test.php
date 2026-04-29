<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Users_Query_Dto;
use WP_REST_Response;

class Api_Users_Test extends API_TestCase {

	protected $api;
	protected $mock_users_service;

	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Users();
		$this->mock_users_service = Mockery::mock( STOLMC_Service_Tracker_Users_Service::class );
		set_private_property( $this->api, 'users_service', $this->mock_users_service );
	}

	public function test_read_returns_paginated_response(): void {
		$this->mock_users_service
			->shouldReceive( 'get_users' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Users_Query_Dto::class ) )
			->andReturn(
				STOLMC_Service_Tracker_Service_Result_Dto::ok(
					[
						'data'     => [ [ 'id' => 1, 'name' => 'User One' ] ],
						'total'    => 1,
						'page'     => 1,
						'per_page' => 6,
					],
					200
				)
			);

		$response = $this->api->read( $this->create_mock_request() );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( 1, $response->get_data()['meta']['pagination']['total'] );
		$this->assertSame( 1, $response->get_data()['meta']['pagination']['total_pages'] );
	}

	public function test_read_staff_returns_passthrough_payload(): void {
		$staff = [
			[ 'id' => 10, 'name' => 'Admin' ],
		];

		$this->mock_users_service
			->shouldReceive( 'get_staff_users' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( $staff, 200 ) );

		$response = $this->api->read_staff( $this->create_mock_request() );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( $staff, $response->get_data()['data'] );
	}
}
