<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Delete_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Update_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Users_Query_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Users_Repository;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_WordPress_Transaction;

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
	 * Create a new user.
	 *
	 * @param STOLMC_Service_Tracker_User_Create_Dto $create_dto Create DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function create_user( STOLMC_Service_Tracker_User_Create_Dto $create_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$user_data = $create_dto->to_array();

			// Check if user already exists - match old API contract (409 status).
			if ( \email_exists( $user_data['email'] ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'email_exists',
					'A user with this email already exists',
					409
				);
			}

			// Set defaults.
			$user_data['role']     = $user_data['role'] ?? 'stolmc_customer';
			$user_data['username'] = $user_data['username'] ?? $user_data['email'];
			$user_data['password'] = $user_data['password'] ?? \wp_generate_password( 12, true, true );
			$has_phone             = array_key_exists( 'phone', $user_data );
			$has_cellphone         = array_key_exists( 'cellphone', $user_data );
			$phone_value           = $user_data['phone'] ?? null;
			$cellphone_value       = $user_data['cellphone'] ?? null;
			unset( $user_data['phone'], $user_data['cellphone'] );

			/**
			 * Filters the user data before insertion.
			 *
			 * @since 1.0.0
			 *
			 * @param array $user_data The user data to insert.
			 */
			$user_data = apply_filters( 'stolmc_service_tracker_user_create_data', $user_data );
			$transaction = new STOLMC_Service_Tracker_WordPress_Transaction();
			if ( ! $transaction->in_transaction() ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for user creation',
					500
				);
			}

			$user_id = $this->users_repository->create( $user_data );

			if ( is_wp_error( $user_id ) ) {
				$transaction->rollback();

				/**
				 * Fires when a user creation fails.
				 *
				 * @since 1.0.0
				 *
				 * @param \WP_Error $user_id   The error object.
				 * @param array    $user_data The user data that failed.
				 */
				do_action( 'stolmc_service_tracker_user_create_failed', $user_id, $user_data );

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_creation_failed',
					'Failed to create user: ' . $user_id->get_error_message(),
					500
				);
			}

			if ( $has_phone ) {
				$phone_updated = $this->users_repository->update_meta( (int) $user_id, 'phone', $phone_value );
				if ( false === $phone_updated ) {
					$transaction->rollback();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'user_meta_update_failed',
						'Failed to persist user phone metadata',
						500
					);
				}
			}

			if ( $has_cellphone ) {
				$cellphone_updated = $this->users_repository->update_meta( (int) $user_id, 'cellphone', $cellphone_value );
				if ( false === $cellphone_updated ) {
					$transaction->rollback();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'user_meta_update_failed',
						'Failed to persist user cellphone metadata',
						500
					);
				}
			}

			if ( ! $transaction->commit() ) {
				$transaction->rollback();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for user creation',
					500
				);
			}

			/**
			 * Fires after a user has been created.
			 *
			 * @since 1.0.0
			 *
			 * @param int   $user_id   The ID of the created user.
			 * @param array $user_data The user data.
			 */
			do_action( 'stolmc_service_tracker_user_created', $user_id, $user_data );
			do_action( 'stolmc_service_tracker_user_created_with_meta', $user_id, $user_data );

			$data = [
				'id' => $user_id,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 201, 'User created successfully' );
		} catch ( \Exception $e ) {
			if ( isset( $transaction ) ) {
				$transaction->rollback();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
			'user_creation_error',
			'Failed to create user: ' . $e->getMessage(),
			500
			);
		}
	}

	/**
	 * Update an existing user.
	 *
	 * @param STOLMC_Service_Tracker_User_Update_Dto $update_dto Update DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function update_user( STOLMC_Service_Tracker_User_Update_Dto $update_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$user_id     = $update_dto->user_id;
			$update_data = $update_dto->to_array();

			// Check if user exists.
			$user = \get_user_by( 'id', $user_id );
			if ( ! $user ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_not_found',
					'User not found',
					404
				);
			}

			$condition = [ 'ID' => $user_id ];

			/**
			 * Filters the update data before the update operation.
			 *
			 * @since 1.0.0
			 *
			 * @param array $update_data The data to update.
			 * @param array $condition   The WHERE condition.
			 */
			$update_data = apply_filters( 'stolmc_service_tracker_user_update_data', $update_data, $condition );
			$has_phone   = array_key_exists( 'phone', $update_data );
			$has_cellphone = array_key_exists( 'cellphone', $update_data );
			$phone_value   = $update_data['phone'] ?? null;
			$cellphone_value = $update_data['cellphone'] ?? null;
			unset( $update_data['phone'], $update_data['cellphone'] );
			$transaction = new STOLMC_Service_Tracker_WordPress_Transaction();
			if ( ! $transaction->in_transaction() ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for user update',
					500
				);
			}

			$response = $this->users_repository->update( $user_id, $update_data );

			if ( is_wp_error( $response ) ) {
				$transaction->rollback();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_update_failed',
					'Failed to update user: ' . $response->get_error_message(),
					500
				);
			}

			if ( $has_phone ) {
				$phone_updated = $this->users_repository->update_meta( $user_id, 'phone', $phone_value );
				if ( false === $phone_updated ) {
					$transaction->rollback();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'user_meta_update_failed',
						'Failed to persist user phone metadata',
						500
					);
				}
			}

			if ( $has_cellphone ) {
				$cellphone_updated = $this->users_repository->update_meta( $user_id, 'cellphone', $cellphone_value );
				if ( false === $cellphone_updated ) {
					$transaction->rollback();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'user_meta_update_failed',
						'Failed to persist user cellphone metadata',
						500
					);
				}
			}

			if ( ! $transaction->commit() ) {
				$transaction->rollback();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for user update',
					500
				);
			}

			/**
			 * Fires after a user has been updated.
			 *
			 * @since 1.0.0
			 *
			 * @param int|WP_Error $response    The update result.
			 * @param array        $update_data The data that was updated.
			 * @param array        $condition   The WHERE condition.
			 */
			do_action( 'stolmc_service_tracker_user_updated', $response, $update_data, $condition );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( [], 200, 'User updated successfully' );
		} catch ( \Exception $e ) {
			if ( isset( $transaction ) ) {
				$transaction->rollback();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
			'user_update_error',
			'Failed to update user: ' . $e->getMessage(),
			500
			);
		}
	}

	/**
	 * Delete a user.
	 *
	 * @param STOLMC_Service_Tracker_User_Delete_Dto $delete_dto Delete DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function delete_user( STOLMC_Service_Tracker_User_Delete_Dto $delete_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		$transaction = null;

		try {
			$user_id = $delete_dto->user_id;
			// Check if user exists.
			$user = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_not_found',
					'User not found',
					404
				);
			}

			$transaction = new STOLMC_Service_Tracker_WordPress_Transaction();
			if ( ! $transaction->in_transaction() ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for user deletion',
					500
				);
			}

			/**
			 * Fires before a user is deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param int $user_id The ID of the user to delete.
			 */
			do_action( 'stolmc_service_tracker_user_before_delete', $user_id );

			$delete = $this->users_repository->delete( $user_id );

			if ( is_wp_error( $delete ) ) {
				$transaction->rollback();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'user_deletion_failed',
					'Failed to delete user: ' . $delete->get_error_message(),
					500
				);
			}

			if ( ! $transaction->commit() ) {
				$transaction->rollback();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for user deletion',
					500
				);
			}

			/**
			 * Fires after a user has been deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param mixed $delete  The delete result.
			 * @param int   $user_id The ID of the deleted user.
			 */
			do_action( 'stolmc_service_tracker_user_deleted', $delete, $user_id );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( [], 200, 'User deleted successfully' );
		} catch ( \Exception $e ) {
			if ( $transaction instanceof STOLMC_Service_Tracker_WordPress_Transaction ) {
				$transaction->rollback();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'user_deletion_error',
				'Failed to delete user: ' . $e->getMessage(),
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
