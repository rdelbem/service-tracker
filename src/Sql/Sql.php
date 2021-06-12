<?php
namespace ServiceTracker\Sql;

defined( 'ABSPATH' ) or die( 'You do not have permission to access this file on its own.' );
/**
 * Class which has helper functions to get data from the database
 */

class Sql {
	/**
	 * The current table name
	 *
	 * @var boolean
	 */
	private $tableName = false;

	/**
	 * Constructor for the database class to inject the table name
	 *
	 * @param String $tableName - The current table name
	 */
	public function __construct( $tableName ) {
		 $this->tableName = $tableName;
	}

	/**
	 * Insert data into the current data
	 *
	 * @param  array $data - Data to enter into the database table
	 *
	 * @return InsertQuery Object
	 */
	public function insert( array $data ) {
		 global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		try {

			$wpdb->insert( $this->tableName, $data );

			return 'Success, data was inserted at id ' . $wpdb->insert_id;

		} catch ( \Throwable $th ) {

			return 'Error: ' . $th;

		}

	}

	/**
	 * Get all from the selected table
	 *
	 * @param  String $orderBy - Order by column name
	 *
	 * @return Table result
	 */
	public function get_all( $orderBy = null ) {
		global $wpdb;

		$sql = 'SELECT * FROM `' . $this->tableName . '`';

		if ( ! empty( $orderBy ) ) {
			$sql .= ' ORDER BY ' . $orderBy;
		}

		$all = $wpdb->get_results( $sql );

		return $all;
	}

	/**
	 * Get a value by a condition
	 *
	 * @param  Array  $conditionValue - A key value pair of the conditions you want to search on
	 * @param  String $condition - A string value for the condition of the query default to equals
	 *
	 * @return Table result
	 */
	public function get_by( array $conditionValue, $condition = '=' ) {
		 global $wpdb;

		$sql = 'SELECT * FROM `' . $this->tableName . '` WHERE ';

		foreach ( $conditionValue as $field => $value ) {
			switch ( strtolower( $condition ) ) {
				case 'in':
					if ( ! is_array( $value ) ) {
						throw new Exception( 'Values for IN query must be an array.', 1 );
					}

					$sql .= $wpdb->prepare( '`%s` IN (%s)', $field, implode( ',', $value ) );
					break;

				default:
					$sql .= $wpdb->prepare( '`' . $field . '` ' . $condition . ' %s', $value );
					break;
			}
		}

		$result = $wpdb->get_results( $sql );

		return $result;
	}

	/**
	 * Update a table record in the database
	 *
	 * @param  array $data           - Array of data to be updated
	 * @param  array $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Updated object
	 */
	public function update( array $data, array $conditionValue ) {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		$updated = $wpdb->update( $this->tableName, $data, $conditionValue );

		return $updated;
	}

	/**
	 * Delete row on the database table
	 *
	 * @param  array $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Int - Num rows deleted
	 */
	public function delete( array $conditionValue ) {
		global $wpdb;

		$deleted = $wpdb->delete( $this->tableName, $conditionValue );

		return $deleted;
	}
}

