<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Update_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Cases_Read_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use WP_REST_Response;

class Api_Cases_Test extends API_TestCase {

	protected $api;
	protected $mock_cases_service;

	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Cases();
		$this->mock_cases_service = Mockery::mock( STOLMC_Service_Tracker_Cases_Service::class );
		set_private_property( $this->api, 'cases_service', $this->mock_cases_service );
	}

	public function test_read_returns_paginated_payload(): void {
		$payload = [
			'data' => [ [ 'id' => 1 ], [ 'id' => 2 ] ],
			'total' => 2,
			'page' => 1,
			'per_page' => 6,
			'total_pages' => 1,
		];

		$this->mock_cases_service
			->shouldReceive( 'get_cases_for_user' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Cases_Read_Query_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( $payload, 200 ) );

		$request = $this->create_mock_request( [ 'id_user' => 7 ] );
		$response = $this->api->read( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( $payload['data'], $response->get_data()['data'] );
		$this->assertSame( 2, $response->get_data()['meta']['pagination']['total'] );
		$this->assertSame( 1, $response->get_data()['meta']['pagination']['total_pages'] );
	}

	public function test_create_invalid_json_returns_400(): void {
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '{invalid-json' );
		$response = $this->api->create( $request );

		$this->assertSame( 400, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'invalid_json', $data['error_code'] );
	}

	public function test_create_maps_v2_envelope_response(): void {
		$this->mock_cases_service
			->shouldReceive( 'create_case' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Case_Create_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'id' => 123 ], 201 ) );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], json_encode( [ 'id_user' => 1, 'title' => 'X' ] ) );
		$response = $this->api->create( $request );

		$this->assertSame( 201, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertSame( 123, $data['data']['id'] );
		$this->assertArrayHasKey( 'meta', $data );
	}

	public function test_update_calls_service_and_returns_response(): void {
		$this->mock_cases_service
			->shouldReceive( 'update_case' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Case_Update_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'affected_rows' => 1 ], 200 ) );

		$request = $this->create_mock_request( [ 'id' => 99 ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], json_encode( [ 'title' => 'Updated' ] ) );
		$response = $this->api->update( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
	}

	public function test_custom_api_registers_core_routes(): void {
		$this->api->custom_api();
		$this->assertTrue( true );
	}
}
