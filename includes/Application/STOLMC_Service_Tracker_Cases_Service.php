<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Cases_Read_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Cases_Search_Query_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Update_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Delete_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository;

/**
 * Cases Service for business logic operations on cases.
 *
 * This service encapsulates all business logic for cases operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Cases_Service {

	/**
	 * Cases Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Cases_Repository
	 */
	private $cases_repository;

	/**
	 * Transient key for the cases search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_case_search_index';

	/**
	 * How long (in seconds) the cases search index transient lives.
	 * Default: 1 hour.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TTL = 3600;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Cases_Repository|null $cases_repository Cases repository.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Cases_Repository $cases_repository = null ) {
		$this->cases_repository = $cases_repository ?? new STOLMC_Service_Tracker_Cases_Repository();
	}

	/**
	 * Get paginated cases for a user.
	 *
	 * @param STOLMC_Service_Tracker_Cases_Read_Query_Dto $query_dto Query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_cases_for_user( STOLMC_Service_Tracker_Cases_Read_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$user_id  = $query_dto->user_id;
			$page     = $query_dto->page;
			$per_page = $query_dto->per_page;

			$total = $this->cases_repository->count_by_user( $user_id );
			$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

			// Clamp page to valid range.
			$page   = min( $page, $total_pages );
			$offset = ( $page - 1 ) * $per_page;

			$cases = $this->cases_repository->find_paginated_by_user( $user_id, $per_page, $offset );

			$data = [
				'data'        => $cases,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			];

			/**
			 * Filters the cases read response data.
			 *
			 * @since 1.0.0
			 *
			 * @param array $data The cases data with pagination metadata.
			 * @param int   $user_id The user ID.
			 * @param int   $page The page number.
			 * @param int   $per_page Items per page.
			 */
			$data = apply_filters( 'stolmc_service_tracker_cases_read_response', $data, $user_id, $page, $per_page );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'cases_read_error',
				'Failed to read cases: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Search cases using the inverted index.
	 *
	 * @param STOLMC_Service_Tracker_Cases_Search_Query_Dto $query_dto Search query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function search_cases( STOLMC_Service_Tracker_Cases_Search_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$query    = mb_strtolower( trim( $query_dto->query ) );
			$user_id  = $query_dto->user_id;
			$page     = $query_dto->page;
			$per_page = $query_dto->per_page;

			// Empty query — fall back to normal paginated read if user_id provided.
			if ( $query === '' && $user_id > 0 ) {
				return $this->get_cases_for_user(
					new STOLMC_Service_Tracker_Cases_Read_Query_Dto( $user_id, $page, $per_page )
				);
			} elseif ( $query === '' ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'empty_search_query',
					'Search query cannot be empty',
					400
				);
			}

			$index = $this->get_search_index();
			$query_tokens = $this->tokenize( $query );

			// Score each case by how many query tokens match index entries.
			$scores = [];

			foreach ( $query_tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					continue;
				}
				foreach ( $index[ $token ] as $entry ) {
					$case_id = $entry['id'];

					// Filter by user_id when provided.
					if ( $user_id > 0 && $entry['id_user'] !== $user_id ) {
						continue;
					}

					if ( ! isset( $scores[ $case_id ] ) ) {
						$scores[ $case_id ] = [
						'score'   => 0,
						'id_user' => $entry['id_user'],
						];
					}
					++$scores[ $case_id ]['score'];
				}
			}

			if ( empty( $scores ) ) {
				$data = [
					'data'        => [],
					'total'       => 0,
					'page'        => 1,
					'per_page'    => $per_page,
					'total_pages' => 1,
				];

				return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
			}

			// Sort by score descending.
			uasort( $scores, static fn( $a, $b ) => $b['score'] <=> $a['score'] );

			$matched_ids = array_keys( $scores );
			$total       = count( $matched_ids );
			$total_pages = max( 1, (int) ceil( $total / $per_page ) );
			$page        = min( $page, $total_pages );
			$paged_ids   = array_slice( $matched_ids, ( $page - 1 ) * $per_page, $per_page );

			if ( empty( $paged_ids ) ) {
				$data = [
					'data'        => [],
					'total'       => $total,
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => $total_pages,
				];

				return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
			}

			$cases = $this->cases_repository->find_by_ids( array_map( 'intval', $paged_ids ) );

			$cases_by_id = [];
			foreach ( $cases as $case ) {
				$cases_by_id[ (int) $case->id ] = $case;
			}

			$ordered_cases = [];
			foreach ( $paged_ids as $id ) {
				if ( isset( $cases_by_id[ (int) $id ] ) ) {
					$ordered_cases[] = $cases_by_id[ (int) $id ];
				}
			}

			/**
			 * Filters the cases search response data.
			 *
			 * @since 1.4.0
			 *
			 * @param array  $cases  The matched case rows.
			 * @param array  $scores The score map (case_id => ['score', 'id_user']).
			 * @param string $query  The original search query.
			 */
			$ordered_cases = apply_filters( 'stolmc_service_tracker_cases_search_response', $ordered_cases, $scores, $query );

			$data = [
				'data'        => $ordered_cases,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'cases_search_error',
				'Failed to search cases: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Create a new case.
	 *
	 * @param STOLMC_Service_Tracker_Case_Create_Dto $create_dto Create DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function create_case( STOLMC_Service_Tracker_Case_Create_Dto $create_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		$transaction_started = false;

		try {
			$case_data = $create_dto->to_array();

			/**
			 * Filters the case data before insertion.
			 *
			 * @since 1.0.0
			 *
			 * @param array $case_data The case data to insert.
			 */
			$case_data = apply_filters( 'stolmc_service_tracker_case_create_data', $case_data );
			$transaction_started = $this->cases_repository->begin_transaction();
			if ( ! $transaction_started ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for case creation',
					500
				);
			}

			$inserted = $this->cases_repository->create( $case_data );
			$insert_id = $this->cases_repository->get_last_insert_id();

			if ( $insert_id > 0 ) {
				if ( ! $this->cases_repository->commit_transaction() ) {
					$this->cases_repository->rollback_transaction();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'transaction_commit_failed',
						'Failed to commit transaction for case creation',
						500
					);
				}

				/**
				 * Fires after a case has been created.
				 *
				 * @since 1.0.0
				 *
				 * @param int   $case_id   The ID of the created case.
				 * @param array $case_data The case data.
				 */
				do_action( 'stolmc_service_tracker_case_created', $insert_id, $case_data );

				$data = [
					'id' => $insert_id,
				];

				return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 201, 'Case created successfully' );
			}

			/**
			 * Fires when a case creation fails.
			 *
			 * @since 1.0.0
			 *
			 * @param string|false $inserted  The error message.
			 * @param array        $case_data The case data that failed.
			 */
			do_action( 'stolmc_service_tracker_case_create_failed', $inserted, $case_data );
			$this->cases_repository->rollback_transaction();

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'case_creation_failed',
				'Failed to create case: ' . ( is_string( $inserted ) ? $inserted : 'Unknown error' ),
				500
			);
		} catch ( \Exception $e ) {
			if ( $transaction_started ) {
				$this->cases_repository->rollback_transaction();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'case_creation_error',
				'Failed to create case: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Update an existing case.
	 *
	 * @param STOLMC_Service_Tracker_Case_Update_Dto $update_dto Update DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function update_case( STOLMC_Service_Tracker_Case_Update_Dto $update_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		$transaction_started = false;

		try {
			$case_id     = $update_dto->case_id;
			$update_data = $update_dto->to_array();

			// Validate date range if both are provided.
			if ( isset( $update_data['start_at'] ) && isset( $update_data['due_at'] )
				&& $update_data['start_at'] > $update_data['due_at'] ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_date_range',
					'start_at must be before or equal to due_at',
					400
				);
			}

			$condition = [ 'id' => $case_id ];

			/**
			 * Filters the update data before the SQL operation.
			 *
			 * @since 1.0.0
			 *
			 * @param array $update_data The data to update.
			 * @param array $condition   The WHERE condition.
			 */
			$update_data = apply_filters( 'stolmc_service_tracker_case_update_data', $update_data, $condition );
			$transaction_started = $this->cases_repository->begin_transaction();
			if ( ! $transaction_started ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for case update',
					500
				);
			}

			$response = $this->cases_repository->update_by_id( $case_id, $update_data );

			if ( false === $response ) {
				$this->cases_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_update_failed',
					'Failed to update case',
					500
				);
			}

			if ( ! $this->cases_repository->commit_transaction() ) {
				$this->cases_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for case update',
					500
				);
			}

			/**
			 * Fires after a case has been updated.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $response    The update result.
			 * @param array          $update_data The data that was updated.
			 * @param array          $condition   The WHERE condition.
			 */
			do_action( 'stolmc_service_tracker_case_updated', $response, $update_data, $condition );

			$data = [
				'affected_rows' => $response,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200, 'Case updated successfully' );
		} catch ( \Exception $e ) {
			if ( $transaction_started ) {
				$this->cases_repository->rollback_transaction();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'case_update_error',
				'Failed to update case: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Delete a case and its associated progress records.
	 *
	 * @param STOLMC_Service_Tracker_Case_Delete_Dto $delete_dto Delete DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function delete_case( STOLMC_Service_Tracker_Case_Delete_Dto $delete_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$case_id = $delete_dto->case_id;

			$transaction_started = $this->cases_repository->begin_transaction();
			if ( ! $transaction_started ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for case deletion',
					500
				);
			}

			/**
			 * Fires before a case is deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param int $case_id The ID of the case to delete.
			 */
			do_action( 'stolmc_service_tracker_case_before_delete', $case_id );

			$delete_case = $this->cases_repository->delete_by_id( $case_id );
			$delete_progress = $this->cases_repository->delete_progress_by_case_id( $case_id );

			if ( false === $delete_case || false === $delete_progress ) {
				$this->cases_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_deletion_failed',
					'Failed to delete case and related progress',
					500
				);
			}

			if ( ! $this->cases_repository->commit_transaction() ) {
				$this->cases_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for case deletion',
					500
				);
			}

			/**
			 * Fires after a case has been deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param mixed $delete_case     The case delete result.
			 * @param mixed $delete_progress The progress delete result.
			 * @param int   $case_id         The ID of the deleted case.
			 */
			do_action( 'stolmc_service_tracker_case_deleted', $delete_case, $delete_progress, $case_id );

			$data = [
				'case_delete'     => $delete_case,
				'progress_delete' => $delete_progress,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200, 'Case deleted successfully' );
		} catch ( \Exception $e ) {
			$this->cases_repository->rollback_transaction();

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'case_deletion_error',
				'Failed to delete case: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get a case by ID.
	 *
	 * @param int $case_id Case ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_case_by_id( int $case_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$case = $this->cases_repository->find_by_id( $case_id );

			if ( null === $case ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_not_found',
					'Case not found',
					404
				);
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $case, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'case_read_error',
				'Failed to read case: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Bust the search index cache.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function bust_search_index(): void {
		delete_transient( self::SEARCH_INDEX_TRANSIENT );
	}

	/**
	 * Build (or retrieve from cache) the inverted search index for cases.
	 *
	 * Structure:
	 * [
	 *   'token' => [ ['id' => case_id, 'id_user' => user_id], ... ],
	 *   ...
	 * ]
	 *
	 * Tokens are lower-cased prefixes derived from the case title and status.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string, array<int, array{id: int, id_user: int}>> The inverted index.
	 */
	private function get_search_index(): array {
		$cached = get_transient( self::SEARCH_INDEX_TRANSIENT );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		// Fetch all cases — id, id_user, title, status only for efficiency.
		$cases = $this->cases_repository->find_all();

		$index = [];

		foreach ( $cases as $case ) {
			$entry  = [
				'id'      => (int) $case->id,
				'id_user' => (int) $case->id_user,
			];
			$tokens = $this->tokenize( $case->title . ' ' . $case->status );

			foreach ( $tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					$index[ $token ] = [];
				}
				// Avoid duplicates.
				$already = false;
				foreach ( $index[ $token ] as $existing ) {
					if ( $existing['id'] === $entry['id'] ) {
						$already = true;
						break;
					}
				}
				if ( ! $already ) {
					$index[ $token ][] = $entry;
				}
			}
		}

		/**
		 * Filters the built cases search index before it is cached.
		 *
		 * @since 1.4.0
		 *
		 * @param array $index The inverted index array.
		 * @param array $cases The raw case rows used to build it.
		 */
		$index = apply_filters( 'stolmc_service_tracker_case_search_index', $index, $cases );

		set_transient( self::SEARCH_INDEX_TRANSIENT, $index, self::SEARCH_INDEX_TTL );

		return $index;
	}

	/**
	 * Tokenize a string into lower-cased prefix substrings for indexing.
	 *
	 * Splits on whitespace and common separators, then emits every prefix of
	 * every word so partial matches work (e.g. "rep" matches "repair").
	 *
	 * @since 1.4.0
	 *
	 * @param string $text The text to tokenize.
	 * @return string[]    Array of unique tokens.
	 */
	private function tokenize( string $text ): array {
		$text  = mb_strtolower( $text );
		$parts = preg_split( '/[\s@._\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( false === $parts ) {
			return [];
		}

		$tokens = [];

		foreach ( $parts as $part ) {
			$len = mb_strlen( $part );
			for ( $i = 1; $i <= $len; $i++ ) {
				$tokens[] = mb_substr( $part, 0, $i );
			}
		}

		return array_unique( $tokens );
	}
}
