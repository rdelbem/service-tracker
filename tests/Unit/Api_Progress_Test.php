<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Case_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Delete_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Upload_Request_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use WP_REST_Response;

class Api_Progress_Test extends API_TestCase {

	protected $api;
	protected $mock_progress_service;

	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Progress();
		$this->mock_progress_service = Mockery::mock( STOLMC_Service_Tracker_Progress_Service::class );
		set_private_property( $this->api, 'progress_service', $this->mock_progress_service );
	}

	public function test_read_returns_passthrough_progress_list(): void {
		$payload = [
			[ 'id' => 1, 'id_case' => 10, 'text' => 'One' ],
			[ 'id' => 2, 'id_case' => 10, 'text' => 'Two' ],
		];

		$this->mock_progress_service
			->shouldReceive( 'get_progress_for_case' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Progress_Case_Query_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( $payload, 200 ) );

		$request = $this->create_mock_request( [ 'id_case' => 10 ] );
		$response = $this->api->read( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( $payload, $response->get_data()['data'] );
	}

	public function test_create_invalid_json_returns_400(): void {
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '{invalid-json' );
		$response = $this->api->create( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertFalse( $response->get_data()['success'] );
	}

	public function test_create_calls_service_and_maps_v2_response(): void {
		$this->mock_progress_service
			->shouldReceive( 'create_progress' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Progress_Create_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'id' => 50 ], 201 ) );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], json_encode( [ 'id_case' => 10, 'id_user' => 3, 'text' => 'Hello' ] ) );
		$response = $this->api->create( $request );

		$this->assertSame( 201, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
	}

	public function test_delete_calls_service(): void {
		$this->mock_progress_service
			->shouldReceive( 'delete_progress' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Progress_Delete_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'affected_rows' => 1 ], 200 ) );

		$request = $this->create_mock_request( [ 'id' => 7 ] );
		$response = $this->api->delete( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
	}

	public function test_upload_file_delegates_to_service_and_maps_response(): void {
		$this->mock_progress_service
			->shouldReceive( 'handle_upload_request' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Progress_Upload_Request_Dto::class ) )
			->andReturn(
				new STOLMC_Service_Tracker_Service_Result_Dto(
					true,
					[
						'files' => [
							[ 'name' => 'a.txt', 'type' => 'text/plain', 'url' => 'http://x/a.txt', 'size' => 1 ],
						],
					],
					null,
					'Files uploaded successfully',
					201
				)
			);

		$request = $this->create_mock_request(
			[
				'__files' => [
					'files' => [
						'name'     => [ 'a.txt' ],
						'type'     => [ 'text/plain' ],
						'tmp_name' => [ '/tmp/a.txt' ],
						'error'    => [ 0 ],
						'size'     => [ 1 ],
					],
				],
			]
		);

		$response = $this->api->upload_file( $request );

		$this->assertSame( 201, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( 'Files uploaded successfully', $response->get_data()['message'] );
	}

	public function test_custom_api_registers_routes(): void {
		$this->api->custom_api();
		$this->assertTrue( true );
	}
}
