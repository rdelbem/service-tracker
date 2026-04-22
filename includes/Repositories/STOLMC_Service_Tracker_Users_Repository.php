<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Dto;
use WP_Error;

/**
 * Users Repository for WordPress user and user-meta access.
 */
class STOLMC_Service_Tracker_Users_Repository {
	/**
	 * Default role used by Service Tracker client listing/search.
	 *
	 * @var string
	 */
	private const DEFAULT_ROLE = 'customer';

	/**
	 * Default ordering for user list/search.
	 *
	 * @var string
	 */
	private const DEFAULT_ORDERBY = 'display_name';

	/**
	 * Default order direction for user list/search.
	 *
	 * @var string
	 */
	private const DEFAULT_ORDER = 'ASC';

	/**
	 * Get users by query arguments.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 *
	 * @return array<int, mixed>
	 */
	public function find_by( array $args ): array {
		$users = get_users( $args );
		if ( ! is_array( $users ) ) {
			return [];
		}

		$mapped = [];

		foreach ( $users as $user ) {
			if ( is_object( $user ) || is_array( $user ) ) {
				$mapped[] = STOLMC_Service_Tracker_User_Dto::from_user( $user );
				continue;
			}

			// Keep scalar values (e.g., IDs for fields=ids) untouched.
			$mapped[] = $user;
		}

		return $mapped;
	}

	/**
	 * Find user by email.
	 *
	 * @param string $email User email.
	 *
	 * @return STOLMC_Service_Tracker_User_Dto|false
	 */
	public function find_by_email( string $email ): STOLMC_Service_Tracker_User_Dto|false {
		$user = get_user_by( 'email', $email );
		if ( false === $user ) {
			return false;
		}

		return STOLMC_Service_Tracker_User_Dto::from_user( $user );
	}

	/**
	 * Find user by ID.
	 *
	 * @param int $id User ID.
	 *
	 * @return STOLMC_Service_Tracker_User_Dto|false
	 */
	public function find_by_id( int $id ): STOLMC_Service_Tracker_User_Dto|false {
		$user = get_user_by( 'id', $id );
		if ( false === $user ) {
			return false;
		}

		return STOLMC_Service_Tracker_User_Dto::from_user( $user );
	}

	/**
	 * Insert a WordPress user.
	 *
	 * @param array<string, mixed> $user_data User data.
	 *
	 * @return int|WP_Error
	 */
	public function create( array $user_data ): int|WP_Error {
		$normalized = $this->normalize_wp_user_data( $user_data );

		return wp_insert_user( $normalized );
	}

	/**
	 * Update an existing WordPress user.
	 *
	 * @param int                 $user_id User ID.
	 * @param array<string,mixed> $user_data User data.
	 *
	 * @return int|WP_Error
	 */
	public function update( int $user_id, array $user_data ): int|WP_Error {
		$normalized       = $this->normalize_wp_user_data( $user_data );
		$normalized['ID'] = $user_id;

		return wp_update_user( $normalized );
	}

	/**
	 * Delete a WordPress user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool|WP_Error
	 */
	public function delete( int $user_id ): bool|WP_Error {
		if ( ! function_exists( 'wp_delete_user' ) && defined( 'ABSPATH' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$reassign = get_current_user_id();
		$reassign = ( $reassign > 0 && $reassign !== $user_id ) ? $reassign : null;

		return wp_delete_user( $user_id, $reassign );
	}

	/**
	 * Update a user meta key.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 *
	 * @return int|bool
	 */
	public function update_meta( int $user_id, string $key, mixed $value ): int|bool {
		return update_user_meta( $user_id, $key, $value );
	}

	/**
	 * Read a user meta value.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Whether to return single value.
	 *
	 * @return mixed
	 */
	public function find_meta( int $user_id, string $key, bool $single = true ): mixed {
		return get_user_meta( $user_id, $key, $single );
	}

	/**
	 * Backward-compatible alias for find_by().
	 *
	 * @param array<string, mixed> $args Query arguments.
	 *
	 * @return array<int, mixed>
	 */
	public function get_users( array $args ): array {
		return $this->find_by( $args );
	}

	/**
	 * Backward-compatible alias for find_by_email().
	 *
	 * @param string $email User email.
	 *
	 * @return STOLMC_Service_Tracker_User_Dto|false
	 */
	public function get_user_by_email( string $email ): STOLMC_Service_Tracker_User_Dto|false {
		return $this->find_by_email( $email );
	}

	/**
	 * Backward-compatible alias for find_by_id().
	 *
	 * @param int $id User ID.
	 *
	 * @return STOLMC_Service_Tracker_User_Dto|false
	 */
	public function get_user_by_id( int $id ): STOLMC_Service_Tracker_User_Dto|false {
		return $this->find_by_id( $id );
	}

	/**
	 * Backward-compatible alias for create().
	 *
	 * @param array<string, mixed> $user_data User data.
	 *
	 * @return int|WP_Error
	 */
	public function insert_user( array $user_data ): int|WP_Error {
		return $this->create( $user_data );
	}

	/**
	 * Backward-compatible alias for update_meta().
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 *
	 * @return int|bool
	 */
	public function update_user_meta( int $user_id, string $key, mixed $value ): int|bool {
		return $this->update_meta( $user_id, $key, $value );
	}

	/**
	 * Backward-compatible alias for find_meta().
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Whether to return single value.
	 *
	 * @return mixed
	 */
	public function get_user_meta( int $user_id, string $key, bool $single = true ): mixed {
		return $this->find_meta( $user_id, $key, $single );
	}

	/**
	 * Count all customer users.
	 *
	 * @return int
	 */
	public function count_all(): int {
		$args = [
			'role'    => self::DEFAULT_ROLE,
			'fields'  => 'ids',
			'orderby' => self::DEFAULT_ORDERBY,
			'order'   => self::DEFAULT_ORDER,
		];

		$args = apply_filters( 'stolmc_service_tracker_get_users_count_args', $args );
		$ids  = get_users( $args );

		return is_array( $ids ) ? count( $ids ) : 0;
	}

	/**
	 * Fetch paginated customer users.
	 *
	 * @param int $per_page Items per page.
	 * @param int $offset   Offset.
	 *
	 * @return array<int, STOLMC_Service_Tracker_User_Dto>
	 */
	public function find_paginated( int $per_page, int $offset ): array {
		$args = [
			'role'    => self::DEFAULT_ROLE,
			'orderby' => self::DEFAULT_ORDERBY,
			'order'   => self::DEFAULT_ORDER,
			'number'  => max( 1, $per_page ),
			'offset'  => max( 0, $offset ),
		];

		$args  = apply_filters( 'stolmc_service_tracker_get_users_args', $args );
		$users = get_users( $args );

		if ( ! is_array( $users ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static fn( $user ) => ( is_object( $user ) || is_array( $user ) )
						? STOLMC_Service_Tracker_User_Dto::from_user( $user )
						: null,
					$users
				)
			)
		);
	}

	/**
	 * Fetch users by a list of IDs while preserving order from get_users(include).
	 *
	 * @param array<int, int> $ids User IDs.
	 *
	 * @return array<int, STOLMC_Service_Tracker_User_Dto>
	 */
	public function find_by_ids( array $ids ): array {
		$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
		if ( [] === $ids ) {
			return [];
		}

		$users = get_users(
			[
				'include' => $ids,
				'role'    => self::DEFAULT_ROLE,
				'orderby' => 'include',
			]
		);

		if ( ! is_array( $users ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static fn( $user ) => ( is_object( $user ) || is_array( $user ) )
						? STOLMC_Service_Tracker_User_Dto::from_user( $user )
						: null,
					$users
				)
			)
		);
	}

	/**
	 * Fetch all customer users.
	 *
	 * @return array<int, STOLMC_Service_Tracker_User_Dto>
	 */
	public function find_all(): array {
		$users = get_users(
			[
				'role'    => self::DEFAULT_ROLE,
				'orderby' => self::DEFAULT_ORDERBY,
				'order'   => self::DEFAULT_ORDER,
			]
		);

		if ( ! is_array( $users ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static fn( $user ) => ( is_object( $user ) || is_array( $user ) )
						? STOLMC_Service_Tracker_User_Dto::from_user( $user )
						: null,
					$users
				)
			)
		);
	}

	/**
	 * Fetch all staff/admin users.
	 *
	 * @return array<int, STOLMC_Service_Tracker_User_Dto>
	 */
	public function find_staff(): array {
		$users = get_users(
			[
				'role__in' => [ 'administrator', 'staff' ],
				'orderby'  => self::DEFAULT_ORDERBY,
				'order'    => self::DEFAULT_ORDER,
			]
		);

		if ( ! is_array( $users ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static fn( $user ) => ( is_object( $user ) || is_array( $user ) )
						? STOLMC_Service_Tracker_User_Dto::from_user( $user )
						: null,
					$users
				)
			)
		);
	}

	/**
	 * Normalize app payload keys into WordPress user payload keys.
	 *
	 * @param array<string,mixed> $user_data Raw user payload.
	 *
	 * @return array<string,mixed>
	 */
	private function normalize_wp_user_data( array $user_data ): array {
		if ( isset( $user_data['name'] ) && ! isset( $user_data['display_name'] ) ) {
			$user_data['display_name'] = (string) $user_data['name'];
		}

		if ( isset( $user_data['email'] ) && ! isset( $user_data['user_email'] ) ) {
			$user_data['user_email'] = (string) $user_data['email'];
		}

		if ( isset( $user_data['username'] ) && ! isset( $user_data['user_login'] ) ) {
			$user_data['user_login'] = (string) $user_data['username'];
		}

		if ( isset( $user_data['password'] ) && ! isset( $user_data['user_pass'] ) ) {
			$user_data['user_pass'] = (string) $user_data['password'];
		}

		if ( ! isset( $user_data['user_login'] ) && isset( $user_data['user_email'] ) ) {
			$user_data['user_login'] = (string) $user_data['user_email'];
		}

		unset( $user_data['name'], $user_data['email'], $user_data['username'], $user_data['password'] );

		return $user_data;
	}
}
