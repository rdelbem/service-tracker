<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Progress DTO.
 */
class STOLMC_Service_Tracker_Progress_Dto {

	/**
	 * Progress ID.
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
	 * Case ID.
	 *
	 * @var int
	 */
	public $id_case;

	/**
	 * Progress text.
	 *
	 * @var string
	 */
	public $text;

	/**
	 * Attachments payload.
	 *
	 * @var mixed
	 */
	public $attachments;

	/**
	 * Optional created date.
	 *
	 * @var string|null
	 */
	public $created_at;

	/**
	 * Constructor.
	 *
	 * @param int         $id Progress ID.
	 * @param int         $id_user User ID.
	 * @param int         $id_case Case ID.
	 * @param string      $text Progress text.
	 * @param mixed       $attachments Attachments payload.
	 * @param string|null $created_at Optional created date.
	 */
	public function __construct( int $id, int $id_user, int $id_case, string $text, mixed $attachments = null, ?string $created_at = null ) {
		$this->id          = $id;
		$this->id_user     = $id_user;
		$this->id_case     = $id_case;
		$this->text        = $text;
		$this->attachments = $attachments;
		$this->created_at  = $created_at;
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
		$id_case     = self::read_int( $row, 'id_case' );
		$text        = self::read_string( $row, 'text' );
		$attachments = self::read_mixed( $row, 'attachments' );
		$created_at  = self::read_nullable_string( $row, 'created_at' );

		return new self( $id, $id_user, $id_case, $text, $attachments, $created_at );
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
	 * Read mixed value from row.
	 *
	 * @param object|array<int|string, mixed> $row Raw row.
	 * @param string                      $key Field key.
	 *
	 * @return mixed
	 */
	private static function read_mixed( object|array $row, string $key ): mixed {
		if ( is_array( $row ) ) {
			return $row[ $key ] ?? null;
		}

		return $row->{$key} ?? null;
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
			if ( ! isset( $row[ $key ] ) || null === $row[ $key ] ) {
				return null;
			}

			return (string) $row[ $key ];
		}

		if ( ! isset( $row->{$key} ) || null === $row->{$key} ) {
			return null;
		}

		return (string) $row->{$key};
	}
}
