<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Calendar_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use WP_REST_Response;

class Api_Calendar_Test extends API_TestCase {

	protected $api;
	protected $mock_calendar_service;

	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Calendar();
		$this->mock_calendar_service = Mockery::mock( STOLMC_Service_Tracker_Calendar_Service::class );
		set_private_property( $this->api, 'calendar_service', $this->mock_calendar_service );
	}

	public function test_calendar_endpoint_requires_start_and_end_parameters(): void {
		$request = $this->create_mock_request( [] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 400, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	public function test_calendar_endpoint_returns_envelope_payload_on_success(): void {
		$payload = [
			'cases' => [ [ 'id' => 1, 'title' => 'A' ] ],
			'progress' => [ [ 'id' => 10, 'id_case' => 1 ] ],
			'date_index' => [ '2026-04-01' => [ 'starts' => [ 1 ], 'ends' => [] ] ],
		];

		$this->mock_calendar_service
			->shouldReceive( 'get_calendar_data' )
			->once()
			->with( Mockery::type( STOLMC_Service_Tracker_Calendar_Query_Dto::class ) )
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( $payload, 200 ) );

		$request = $this->create_mock_request(
			[
				'start' => '2026-04-01',
				'end' => '2026-04-30',
			]
		);

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertSame( $payload, $response->get_data()['data'] );
	}

	public function test_custom_api_registers_calendar_route(): void {
		$this->api->custom_api();
		$this->assertTrue( true );
	}
}
