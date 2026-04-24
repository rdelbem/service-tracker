<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Analytics_Query_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Analytics_Repository;

/**
 * Analytics Service for business logic operations on analytics data.
 *
 * This service encapsulates all business logic for analytics operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Analytics_Service {

	/**
	 * Analytics Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Analytics_Repository
	 */
	private $analytics_repository;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Analytics_Repository|null $analytics_repository Analytics repository.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Analytics_Repository $analytics_repository = null ) {
		$this->analytics_repository = $analytics_repository ?? new STOLMC_Service_Tracker_Analytics_Repository();
	}

	/**
	 * Get aggregated analytics data.
	 *
	 * @param STOLMC_Service_Tracker_Analytics_Query_Dto $query_dto Analytics query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_analytics( STOLMC_Service_Tracker_Analytics_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$start = $query_dto->start;
			$end   = $query_dto->end;

			// Validate date parameters if provided.
			if ( $start !== null && ! $this->is_valid_date( $start ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_date_format',
					'Invalid start date format. Expected YYYY-MM-DD',
					400
				);
			}

			if ( $end !== null && ! $this->is_valid_date( $end ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_date_format',
					'Invalid end date format. Expected YYYY-MM-DD',
					400
				);
			}

			// Validate date range if both dates are provided.
			if ( $start !== null && $end !== null && strtotime( $start ) > strtotime( $end ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_date_range',
					'Start date must be before or equal to end date',
					400
				);
			}

			// Get analytics data from Repository.
			$summary        = $this->analytics_repository->find_summary_stats();
			$customer_stats = $this->analytics_repository->find_customer_stats();
			$admin_stats    = $this->analytics_repository->find_admin_stats();
			$trends         = $this->analytics_repository->find_trends( $start, $end );

			$analytics_data = [
				'summary'        => $summary,
				'customer_stats' => $customer_stats,
				'admin_stats'    => $admin_stats,
				'trends'         => $trends,
			];

			/**
			 * Filters the analytics summary response.
			 *
			 * @since 1.2.0
			 *
			 * @param array $summary The summary statistics.
			 * @param string|null $start Start date.
			 * @param string|null $end End date.
			 */
			$analytics_data['summary'] = apply_filters( 'stolmc_service_tracker_analytics_summary_response', $analytics_data['summary'], $start, $end );

			/**
			 * Filters the analytics customer stats response.
			 *
			 * @since 1.2.0
			 *
			 * @param array $customer_stats The customer statistics.
			 * @param string|null $start Start date.
			 * @param string|null $end End date.
			 */
			$analytics_data['customer_stats'] = apply_filters( 'stolmc_service_tracker_analytics_customer_stats_response', $analytics_data['customer_stats'], $start, $end );

			/**
			 * Filters the analytics admin stats response.
			 *
			 * @since 1.2.0
			 *
			 * @param array $admin_stats The admin statistics.
			 * @param string|null $start Start date.
			 * @param string|null $end End date.
			 */
			$analytics_data['admin_stats'] = apply_filters( 'stolmc_service_tracker_analytics_admin_stats_response', $analytics_data['admin_stats'], $start, $end );

			/**
			 * Filters the analytics trends response.
			 *
			 * @since 1.2.0
			 *
			 * @param array $trends The trend data.
			 * @param string|null $start Start date.
			 * @param string|null $end End date.
			 */
			$analytics_data['trends'] = apply_filters( 'stolmc_service_tracker_analytics_trends_response', $analytics_data['trends'], $start, $end );

			/**
			 * Filters the final analytics payload before returning.
			 *
			 * @since 1.2.0
			 *
			 * @param array $payload The complete analytics payload.
			 * @param string|null $start Start date.
			 * @param string|null $end End date.
			 */
			$analytics_data = apply_filters( 'stolmc_service_tracker_analytics_payload', $analytics_data, $start, $end );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $analytics_data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'analytics_data_error',
				'Failed to get analytics data: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Check if a string is a valid date in YYYY-MM-DD format.
	 *
	 * @param string $date Date string.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private function is_valid_date( string $date ): bool {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) !== 1 ) {
			return false;
		}

		$date_parts = explode( '-', $date );
		if ( count( $date_parts ) !== 3 ) {
			return false;
		}

		list( $year, $month, $day ) = $date_parts;

		return checkdate( (int) $month, (int) $day, (int) $year );
	}
}
