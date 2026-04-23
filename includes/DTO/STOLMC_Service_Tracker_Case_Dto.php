<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Case DTO.
 */
class STOLMC_Service_Tracker_Case_Dto {

	/**
	 * Case ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $id_user;

	/**
	 * Case title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Case status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Optional client name.
	 *
	 * @var string|null
	 */
	public $client_name;

	/**
	 * Optional case created date.
	 *
	 * @var string|null
	 */
	public $created_at;

	/**
	 * Optional case owner user ID.
	 *
	 * @var int|null
	 */
	public $owner_id;

	/**
	 * Optional case description.
	 *
	 * @var string|null
	 */
	public $description;

	/**
	 * Optional case start datetime.
	 *
	 * @var string|null
	 */
	public $start_at;

	/**
	 * Optional case due datetime.
	 *
	 * @var string|null
	 */
	public $due_at;

	/**
	 * Constructor.
	 *
	 * @param int         $id Case ID.
	 * @param int         $id_user User ID.
	 * @param string      $title Case title.
	 * @param string      $status Case status.
	 * @param string|null $client_name Optional client name.
	 * @param string|null $created_at Optional created date.
	 * @param int|null    $owner_id Optional owner user ID.
	 * @param string|null $description Optional description.
	 * @param string|null $start_at Optional start datetime.
	 * @param string|null $due_at Optional due datetime.
	 */
	public function __construct( int $id, int $id_user, string $title, string $status, ?string $client_name = null, ?string $created_at = null, ?int $owner_id = null, ?string $description = null, ?string $start_at = null, ?string $due_at = null ) {
		$this->id          = $id;
		$this->id_user     = $id_user;
		$this->title       = $title;
		$this->status      = $status;
		$this->client_name = $client_name;
		$this->created_at  = $created_at;
		$this->owner_id    = $owner_id;
		$this->description = $description;
		$this->start_at    = $start_at;
		$this->due_at      = $due_at;
	}

	/**
	 * Build DTO from raw row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 *
	 * @return self
	 */
	public static function from_row( object|array $row ): self {
		$id          = self::read_int( $row, 'id' );
		$id_user     = self::read_int( $row, 'id_user' );
		$title       = self::read_string( $row, 'title' );
		$status      = self::read_string( $row, 'status' );
		$client_name = self::read_nullable_string( $row, 'client_name' );
		$created_at  = self::read_nullable_string( $row, 'created_at' );
		$owner_id    = self::read_nullable_int( $row, 'owner_id' );
		$description = self::read_nullable_string( $row, 'description' );
		$start_at    = self::read_nullable_string( $row, 'start_at' );
		$due_at      = self::read_nullable_string( $row, 'due_at' );

		return new self( $id, $id_user, $title, $status, $client_name, $created_at, $owner_id, $description, $start_at, $due_at );
	}

	/**
	 * Read integer key from row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 * @param string                      $key Field key.
	 *
	 * @return int
	 */
	private static function read_int( object|array $row, string $key ): int {
		if ( is_array( $row ) ) {
			return isset( $row[ $key ] ) ? (int) $row[ $key ] : 0;
		}

		return isset( $row->{$key} ) ? (int) $row->{$key} : 0;
	}

	/**
	 * Read string key from row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 * @param string                      $key Field key.
	 *
	 * @return string
	 */
	private static function read_string( object|array $row, string $key ): string {
		if ( is_array( $row ) ) {
			return isset( $row[ $key ] ) ? (string) $row[ $key ] : '';
		}

		return isset( $row->{$key} ) ? (string) $row->{$key} : '';
	}

	/**
	 * Read nullable string key from row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 * @param string                      $key Field key.
	 *
	 * @return string|null
	 */
	private static function read_nullable_string( object|array $row, string $key ): ?string {
		if ( is_array( $row ) ) {
			if ( ! array_key_exists( $key, $row ) || null === $row[ $key ] ) {
				return null;
			}

			return (string) $row[ $key ];
		}

		if ( ! isset( $row->{$key} ) ) {
			return null;
		}

		return (string) $row->{$key};
	}

	/**
	 * Read nullable integer key from row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 * @param string                      $key Field key.
	 *
	 * @return int|null
	 */
	private static function read_nullable_int( object|array $row, string $key ): ?int {
		if ( is_array( $row ) ) {
			if ( ! array_key_exists( $key, $row ) || null === $row[ $key ] || '' === $row[ $key ] ) {
				return null;
			}

			return (int) $row[ $key ];
		}

		if ( ! isset( $row->{$key} ) || '' === $row->{$key} ) {
			return null;
		}

		return (int) $row->{$key};
	}
}
