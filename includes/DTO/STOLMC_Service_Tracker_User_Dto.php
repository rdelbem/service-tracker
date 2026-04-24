<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * User DTO.
 */
class STOLMC_Service_Tracker_User_Dto {

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $ID;

	/**
	 * Display name.
	 *
	 * @var string
	 */
	public $display_name;

	/**
	 * User email.
	 *
	 * @var string
	 */
	public $user_email;

	/**
	 * Registered datetime.
	 *
	 * @var string
	 */
	public $user_registered;

	/**
	 * Roles.
	 *
	 * @var array<int, string>
	 */
	public $roles;

	/**
	 * Constructor.
	 *
	 * @param int                $id User ID.
	 * @param string             $display_name Display name.
	 * @param string             $user_email User email.
	 * @param string             $user_registered Registered date.
	 * @param array<int, string> $roles User roles.
	 */
	public function __construct( int $id, string $display_name, string $user_email, string $user_registered, array $roles = [] ) {
		$this->ID              = $id;
		$this->display_name    = $display_name;
		$this->user_email      = $user_email;
		$this->user_registered = $user_registered;
		$this->roles           = $roles;
	}

	/**
	 * Build DTO from a WP user object/array.
	 *
	 * @param object|array<string, mixed> $user User data.
	 *
	 * @return self
	 */
	public static function from_user( object|array $user ): self {
		$id              = self::read_int( $user, 'ID' );
		$display_name    = self::read_string( $user, 'display_name' );
		$user_email      = self::read_string( $user, 'user_email' );
		$user_registered = self::read_string( $user, 'user_registered' );
		$roles           = self::read_roles( $user );

		return new self( $id, $display_name, $user_email, $user_registered, $roles );
	}

	/**
	 * Read integer key from data.
	 *
	 * @param object|array<string, mixed> $user User data.
	 * @param string                      $key Field key.
	 *
	 * @return int
	 */
	private static function read_int( object|array $user, string $key ): int {
		if ( is_array( $user ) ) {
			return isset( $user[ $key ] ) ? (int) $user[ $key ] : 0;
		}

		return isset( $user->{$key} ) ? (int) $user->{$key} : 0;
	}

	/**
	 * Read string key from data.
	 *
	 * @param object|array<string, mixed> $user User data.
	 * @param string                      $key Field key.
	 *
	 * @return string
	 */
	private static function read_string( object|array $user, string $key ): string {
		if ( is_array( $user ) ) {
			return isset( $user[ $key ] ) ? (string) $user[ $key ] : '';
		}

		return isset( $user->{$key} ) ? (string) $user->{$key} : '';
	}

	/**
	 * Read roles from user data.
	 *
	 * @param object|array<string, mixed> $user User data.
	 *
	 * @return array<int, string>
	 */
	private static function read_roles( object|array $user ): array {
		$roles = [];

		if ( is_array( $user ) ) {
			$roles = $user['roles'] ?? [];
		} elseif ( isset( $user->roles ) ) {
			$roles = $user->roles;
		}

		if ( ! is_array( $roles ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map( static fn( $role ): string => (string) $role, $roles ),
				static fn( string $role ): bool => '' !== $role
			)
		);
	}
}
