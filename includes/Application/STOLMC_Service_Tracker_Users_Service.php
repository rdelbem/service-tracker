<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Users_Query_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Users_Repository;

/**
 * Users Service for business logic operations on users.
 *
 * This service encapsulates all business logic for user operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Users_Service {

	/**
	 * Users Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Users_Repository
	 */
	private $users_repository;

	/**
	 * Transient key for the user search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_user_search_index';

	/**
	 * How long (in seconds) the search index transient lives.
	 * Default: 1 hour.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TTL = 3600;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Users_Repository|null $users_repository Users repository.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Users_Repository $users_repository = null ) {
		$this->users_repository = $users_repository ?? new STOLMC_Service_Tracker_Users_Repository();
	}

	/**
	 * Get paginated users.
	 *
	 * @param STOLMC_Service_Tracker_Users_Query_Dto $query_dto Query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_users( STOLMC_Service_Tracker_Users_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$page     = max( 1, $query_dto->page );
			$per_page = max( 1, $query_dto->per_page );

			$total = $this->users_repository->count_all();
			$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

			// Clamp page to valid range.
			$page   = min( $page, $total_pages );
			$offset = ( $page - 1 ) * $per_page;

			$users = $this->users_repository->find_paginated( $per_page, $offset );

			$data = [
				'data'        => $users,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			];

			/**
			 * Filters the users read response data.
			 *
			 * @since 1.0.0
			 *
			 * @param array $data The users data with pagination metadata.
			 * @param int   $page The page number.
			 * @param int   $per_page Items per page.
			 */
			$data = apply_filters( 'stolmc_service_tracker_users_read_response', $data, $page, $per_page );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'users_read_error',
				'Failed to read users: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Search users using the inverted index.
	 *
	 * @param STOLMC_Service_Tracker_Users_Query_Dto $query_dto Query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function search_users( STOLMC_Service_Tracker_Users_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$query    = mb_strtolower( trim( $query_dto->query ) );
			$page     = max( 1, $query_dto->page );
			$per_page = max( 1, $query_dto->per_page );

			// Empty query — fall back to normal paginated read.
			if ( '' === $query ) {
				return $this->get_users( $query_dto );
			}

			$index = $this->get_search_index();
			$query_tokens = $this->tokenize( $query );

			// Score each user by how many query tokens match index entries.
			$scores = [];

			foreach ( $query_tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					continue;
				}
				foreach ( $index[ $token ] as $user_id ) {
					if ( ! isset( $scores[ $user_id ] ) ) {
						$scores[ $user_id ] = 0;
					}
					++$scores[ $user_id ];
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
			arsort( $scores );

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

			$users = $this->users_repository->find_by_ids( array_map( 'intval', $paged_ids ) );

				$users_by_id = [];
			foreach ( $users as $user ) {
				$users_by_id[ (int) $user->ID ] = $user;
			}

			$ordered_users = [];
			foreach ( $paged_ids as $id ) {
				if ( isset( $users_by_id[ (int) $id ] ) ) {
					$ordered_users[] = $users_by_id[ (int) $id ];
				}
			}

			/**
			 * Filters the users search response data.
			 *
			 * @since 1.4.0
			 *
			 * @param array  $users The matched user rows.
			 * @param array  $scores The score map (user_id => score).
			 * @param string $query The original search query.
			 */
			$ordered_users = apply_filters( 'stolmc_service_tracker_users_search_response', $ordered_users, $scores, $query );

			$data = [
				'data'        => $ordered_users,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'users_search_error',
				'Failed to search users: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get all staff/admin users.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_staff_users(): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$users = $this->users_repository->find_staff();

			/**
			 * Filters the staff users response data.
			 *
			 * @since 1.0.0
			 *
			 * @param array $users Staff users list.
			 */
			$users = apply_filters( 'stolmc_service_tracker_users_staff_response', $users );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $users, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'users_staff_read_error',
				'Failed to read staff users: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get a user by ID.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_user_by_id( int $user_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$user = $this->users_repository->find_by_id( $user_id );

			if ( ! $user ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_not_found',
					'User not found',
					404
				);
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $user, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'user_read_error',
				'Failed to read user: ' . $e->getMessage(),
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
	 * Build (or retrieve from cache) the inverted search index for users.
	 *
	 * Structure:
	 * [
	 *   'token' => [ user_id, user_id, ... ],
	 *   ...
	 * ]
	 *
	 * Tokens are lower-cased prefixes derived from user display name, email, and meta.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string, array<int, int>> The inverted index.
	 */
	private function get_search_index(): array {
		$cached = get_transient( self::SEARCH_INDEX_TRANSIENT );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		// Fetch all users.
		$users = $this->users_repository->find_all();

		$index = [];

		foreach ( $users as $user ) {
			$user_id = (int) $user->ID;
			$text = $user->display_name . ' ' . $user->user_email . ' ' . ( $user->first_name ?? '' ) . ' ' . ( $user->last_name ?? '' );
			$tokens = $this->tokenize( $text );

			foreach ( $tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					$index[ $token ] = [];
				}
				// Avoid duplicates.
				if ( ! in_array( $user_id, $index[ $token ], true ) ) {
					$index[ $token ][] = $user_id;
				}
			}
		}

		/**
		 * Filters the built user search index before it is cached.
		 *
		 * @since 1.4.0
		 *
		 * @param array $index The inverted index array.
		 * @param array $users The raw user rows used to build it.
		 */
		$index = apply_filters( 'stolmc_service_tracker_user_search_index', $index, $users );

		set_transient( self::SEARCH_INDEX_TRANSIENT, $index, self::SEARCH_INDEX_TTL );

		return $index;
	}

	/**
	 * Tokenize a string into lower-cased prefix substrings for indexing.
	 *
	 * Splits on whitespace and common separators, then emits every prefix of
	 * every word so partial matches work (e.g. "joh" matches "john").
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
