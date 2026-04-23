<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Analytics_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Calendar_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Analytics;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Calendar;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Cases;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Progress;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Toggle;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Users;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Analytics_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use WP_REST_Response;

class Api_Contract_V2_Test extends API_TestCase {

	/**
	 * @return array<string, mixed>
	 */
	private function load_contract(): array {
		$content = file_get_contents( __DIR__ . '/../../docs/api_contract_v2.json' );
		$this->assertIsString( $content );
		$decoded = json_decode( $content, true );
		$this->assertIsArray( $decoded );

		return $decoded;
	}

	/**
	 * @param WP_REST_Response $response
	 */
	private function assert_is_canonical_envelope( WP_REST_Response $response ): void {
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'error_code', $data );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertArrayHasKey( 'meta', $data );
		$this->assertIsBool( $data['success'] );
		$this->assertIsArray( $data['meta'] );
	}

	/**
	 * @param WP_REST_Response     $response
	 * @param array<string, mixed> $contract
	 */
	private function assert_response_matches_contract( WP_REST_Response $response, string $endpoint_key, array $contract ): void {
		$this->assert_is_canonical_envelope( $response );

		$endpoints = $contract['endpoints'];
		$this->assertIsArray( $endpoints );
		$this->assertArrayHasKey( $endpoint_key, $endpoints );
		$endpoint_spec = $endpoints[ $endpoint_key ];

		$this->assertContains( $response->get_status(), $endpoint_spec['statuses'] );

		$payload = $response->get_data();
		if ( $response->get_status() >= 400 ) {
			$this->assertIsString( $payload['error_code'] );
			$this->assertNotSame( '', trim( $payload['error_code'] ) );
			$this->assertIsString( $payload['message'] );
			$this->assertNotSame( '', trim( $payload['message'] ) );
		}

		if ( in_array( 'pagination', $endpoint_spec['meta_required'], true ) && $response->get_status() < 400 ) {
			$this->assertArrayHasKey( 'pagination', $payload['meta'] );
			$this->assertIsArray( $payload['meta']['pagination'] );
			$this->assertArrayHasKey( 'total', $payload['meta']['pagination'] );
			$this->assertArrayHasKey( 'page', $payload['meta']['pagination'] );
			$this->assertArrayHasKey( 'per_page', $payload['meta']['pagination'] );
			$this->assertArrayHasKey( 'total_pages', $payload['meta']['pagination'] );
		}
	}

	public function test_legacy_api_directory_is_empty(): void {
		$legacy_api_dir = __DIR__ . '/../../includes/API';
		$legacy_files   = glob( $legacy_api_dir . '/*.php' );

		$this->assertIsArray( $legacy_files );
		$this->assertSame( [], $legacy_files, 'Legacy includes/API runtime classes must not exist.' );
	}

	public function test_legacy_api_classes_are_not_available(): void {
		$this->assertFalse( class_exists( 'STOLMC_Service_Tracker\\includes\\API\\STOLMC_Service_Tracker_Api_Cases' ) );
		$this->assertFalse( class_exists( 'STOLMC_Service_Tracker\\includes\\API\\STOLMC_Service_Tracker_Api_Users' ) );
	}

	public function test_controller_route_registry_matches_contract_json(): void {
		$contract = $this->load_contract();
		$captured = [];

		Functions\when( 'register_rest_route' )->alias(
			static function ( string $namespace, string $route, array $args ) use ( &$captured ): bool {
				$captured[] = [
					'namespace' => $namespace,
					'route' => $route,
					'methods' => $args['methods'],
				];

				return true;
			}
		);

		$cases = new STOLMC_Service_Tracker_Api_Cases();
		$cases->custom_api();
		$progress = new STOLMC_Service_Tracker_Api_Progress();
		$progress->custom_api();
		$users = new STOLMC_Service_Tracker_Api_Users();
		$users->custom_api();
		$toggle = new STOLMC_Service_Tracker_Api_Toggle();
		$toggle->custom_api();
		$calendar = new STOLMC_Service_Tracker_Api_Calendar();
		$calendar->custom_api();
		$GLOBALS['wpdb']->users = 'wp_users';
		$analytics = new STOLMC_Service_Tracker_Api_Analytics();
		$analytics->run();

		$actual_keys = [];
		foreach ( $captured as $row ) {
			$this->assertSame( 'service-tracker-stolmc/v1', $row['namespace'] );
			$actual_keys[] = $row['methods'] . ' ' . $row['route'];
		}
		sort( $actual_keys );

		$expected_keys = array_keys( $contract['endpoints'] );
		sort( $expected_keys );

		$this->assertSame( $expected_keys, $actual_keys );
	}

	public function test_cases_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$api = new STOLMC_Service_Tracker_Api_Cases();
		$service = Mockery::mock( STOLMC_Service_Tracker_Cases_Service::class );
		set_private_property( $api, 'cases_service', $service );

		$service->shouldReceive( 'get_cases_for_user' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [
				'data' => [ [ 'id' => 1 ] ],
				'total' => 1,
				'page' => 1,
				'per_page' => 6,
				'total_pages' => 1,
			], 200 ) );

		$ok = $api->read( $this->create_mock_request( [ 'id_user' => 7 ] ) );
		$this->assert_response_matches_contract( $ok, 'GET /cases/(?P<id_user>\\d+)', $contract );

		$bad = $api->create( $this->create_mock_request( [ 'id_user' => 7 ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '{invalid-json' ) );
		$this->assert_response_matches_contract( $bad, 'POST /cases/(?P<id_user>\\d+)', $contract );
	}

	public function test_progress_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$api = new STOLMC_Service_Tracker_Api_Progress();
		$service = Mockery::mock( STOLMC_Service_Tracker_Progress_Service::class );
		set_private_property( $api, 'progress_service', $service );

		$service->shouldReceive( 'get_progress_for_case' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ [ 'id' => 1 ] ], 200 ) );

		$ok = $api->read( $this->create_mock_request( [ 'id_case' => 10 ] ) );
		$this->assert_response_matches_contract( $ok, 'GET /progress/(?P<id_case>\\d+)', $contract );

		$bad = $api->create( $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '{invalid-json' ) );
		$this->assert_response_matches_contract( $bad, 'POST /progress/(?P<id_case>\\d+)', $contract );
	}

	public function test_users_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$api = new STOLMC_Service_Tracker_Api_Users();
		$service = Mockery::mock( STOLMC_Service_Tracker_Users_Service::class );
		set_private_property( $api, 'users_service', $service );

		$service->shouldReceive( 'get_users' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [
				'data' => [ [ 'ID' => 1 ] ],
				'total' => 1,
				'page' => 1,
				'per_page' => 6,
				'total_pages' => 1,
			], 200 ) );

		$ok = $api->read( $this->create_mock_request() );
		$this->assert_response_matches_contract( $ok, 'GET /users', $contract );

		$bad = $api->create( $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], 'invalid-json' ) );
		$this->assert_response_matches_contract( $bad, 'POST /users/(?P<id>\\d+)', $contract );
	}

	public function test_toggle_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$api = new STOLMC_Service_Tracker_Api_Toggle();
		$service = Mockery::mock( STOLMC_Service_Tracker_Toggle_Service::class );
		set_private_property( $api, 'toggle_service', $service );

		$service->shouldReceive( 'toggle_case_status' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'case_id' => 100, 'action' => 'closed' ], 200 ) );

		$ok = $api->toggle_status( $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], json_encode( [ 'id' => 100 ] ) ) );
		$this->assert_response_matches_contract( $ok, 'POST /cases-status/(?P<id>\\d+)', $contract );

		$bad = $api->toggle_status( $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], '' ) );
		$this->assert_response_matches_contract( $bad, 'POST /cases-status/(?P<id>\\d+)', $contract );
	}

	public function test_calendar_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$api = new STOLMC_Service_Tracker_Api_Calendar();
		$service = Mockery::mock( STOLMC_Service_Tracker_Calendar_Service::class );
		set_private_property( $api, 'calendar_service', $service );

		$service->shouldReceive( 'get_calendar_data' )
			->once()
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'cases' => [], 'progress' => [], 'date_index' => [] ], 200 ) );

		$ok = $api->get_calendar( $this->create_mock_request( [ 'start' => '2026-04-01', 'end' => '2026-04-30' ] ) );
		$this->assert_response_matches_contract( $ok, 'GET /calendar', $contract );

		$bad = $api->get_calendar( $this->create_mock_request() );
		$this->assert_response_matches_contract( $bad, 'GET /calendar', $contract );
	}

	public function test_analytics_responses_match_contract_success_and_error(): void {
		$contract = $this->load_contract();
		$GLOBALS['wpdb']->users = 'wp_users';
		$api = new STOLMC_Service_Tracker_Api_Analytics();
		$service = Mockery::mock( STOLMC_Service_Tracker_Analytics_Service::class );
		set_private_property( $api, 'analytics_service', $service );

		$service->shouldReceive( 'get_analytics' )
			->once()
			->with(
				Mockery::on(
					static function ( $dto ): bool {
						return $dto instanceof STOLMC_Service_Tracker_Analytics_Query_Dto
							&& null === $dto->start
							&& null === $dto->end;
					}
				)
			)
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::ok( [ 'summary' => [] ], 200 ) );

		$ok = $api->get_analytics( $this->create_mock_request() );
		$this->assert_response_matches_contract( $ok, 'GET /analytics', $contract );

		$service->shouldReceive( 'get_analytics' )
			->once()
			->with(
				Mockery::on(
					static function ( $dto ): bool {
						return $dto instanceof STOLMC_Service_Tracker_Analytics_Query_Dto
							&& 'bad' === $dto->start
							&& 'bad' === $dto->end;
					}
				)
			)
			->andReturn( STOLMC_Service_Tracker_Service_Result_Dto::fail( 'invalid_date_range', 'Invalid date range', 400 ) );

		$bad = $api->get_analytics( $this->create_mock_request( [ 'start' => 'bad', 'end' => 'bad' ] ) );
		$this->assert_response_matches_contract( $bad, 'GET /analytics', $contract );
	}
}
