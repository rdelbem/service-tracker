<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Toggle_Request_Dto;
use WP_REST_Response;

class Api_Toggle_Test extends API_TestCase {

	protected $api;
	protected $mock_toggle_service;

	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Toggle();
		$this->mock_toggle_service = Mockery::mock( STOLMC_Service_Tracker_Toggle_Service::class );
		set_private_property( $this->api, 'toggle_service', $this->mock_toggle_service );
	}

	public function test_toggle_status_returns_400_for_missing_body_id(): void {
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '' );

		$response = $this->api->toggle_status( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 400, $response->get_status() );
		$this->assertFalse( $response->get_data()['success'] );
	}

	public function test_toggle_status_maps_success_service_result(): void {
		$this->mock_toggle_service
			->shouldReceive( 'toggle_case_status' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Toggle_Request_Dto::class ) )
			->andReturn(
				STOLMC_Service_Tracker_Service_Result_Dto::ok(
					[
						'case_id' => self::TEST_CASE_ID,
						'action'  => 'closed',
					],
					200
				)
			);

		$request = $this->create_mock_request(
			[],
			[ 'x_wp_nonce' => [ 'valid_nonce' ] ],
			json_encode( [ 'id' => self::TEST_CASE_ID ] )
		);

		$response = $this->api->toggle_status( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( self::TEST_CASE_ID, $response->get_data()['data']['case_id'] );
	}

	public function test_toggle_status_maps_failure_service_result(): void {
		$this->mock_toggle_service
			->shouldReceive( 'toggle_case_status' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Toggle_Request_Dto::class ) )
			->andReturn(
				STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_not_found',
					'Case not found',
					404
				)
			);

		$request = $this->create_mock_request(
			[],
			[ 'x_wp_nonce' => [ 'valid_nonce' ] ],
			json_encode( [ 'id' => self::TEST_CASE_ID ] )
		);

		$response = $this->api->toggle_status( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 404, $response->get_status() );
		$this->assertFalse( $response->get_data()['success'] );
		$this->assertSame( 'case_not_found', $response->get_data()['error_code'] );
	}
}
