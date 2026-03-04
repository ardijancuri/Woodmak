<?php
/**
 * Shared helpers.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Utils {
	/**
	 * Discount options.
	 *
	 * @return int[]
	 */
	public static function discount_options() {
		return array( 0, 5, 10, 15 );
	}

	/**
	 * Check if user is approved B2B.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_approved_b2b( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$user   = get_user_by( 'id', $user_id );
		$status = get_user_meta( $user_id, '_b2b_status', true );

		return $user instanceof WP_User && in_array( 'b2b_wholesale', (array) $user->roles, true ) && 'approved' === $status;
	}

	/**
	 * Check if user has a pending B2B request.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_pending_b2b( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$user   = get_user_by( 'id', $user_id );
		$status = get_user_meta( $user_id, '_b2b_status', true );

		return $user instanceof WP_User && in_array( 'b2b_pending', (array) $user->roles, true ) && 'pending' === $status;
	}

	/**
	 * Get discount percent for user.
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	public static function get_user_discount_percent( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! self::is_approved_b2b( $user_id ) ) {
			return 0;
		}

		$raw = (int) get_user_meta( $user_id, '_b2b_discount_percent', true );

		return in_array( $raw, self::discount_options(), true ) ? $raw : 0;
	}

	/**
	 * Check B2B-only visibility for current user.
	 *
	 * @return bool
	 */
	public static function current_user_can_view_b2b_only() {
		return self::is_administrator_user() || self::is_approved_b2b();
	}

	/**
	 * Check if user has administrator role.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_administrator_user( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$user = get_user_by( 'id', $user_id );
		return $user instanceof WP_User && in_array( 'administrator', (array) $user->roles, true );
	}

	/**
	 * Check if product is B2B-only.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function is_product_b2b_only( $product_id ) {
		return 'yes' === get_post_meta( absint( $product_id ), '_b2b_only', true );
	}

	/**
	 * Merge WP query-style clauses while preserving relation.
	 *
	 * @param array  $existing Existing query clauses.
	 * @param array  $new_clauses New clauses.
	 * @param string $relation Relation, AND by default.
	 * @return array
	 */
	public static function merge_query_clauses( $existing, $new_clauses, $relation = 'AND' ) {
		$existing = is_array( $existing ) ? $existing : array();
		$result = array();

		if ( isset( $existing['relation'] ) && in_array( $existing['relation'], array( 'AND', 'OR' ), true ) ) {
			$relation = $existing['relation'];
			unset( $existing['relation'] );
		}

		foreach ( (array) $existing as $clause ) {
			if ( is_array( $clause ) && ! empty( $clause ) ) {
				$result[] = $clause;
			}
		}

		foreach ( (array) $new_clauses as $clause ) {
			if ( is_array( $clause ) && ! empty( $clause ) ) {
				$result[] = $clause;
			}
		}

		if ( count( $result ) > 1 ) {
			$result['relation'] = $relation;
		}

		return $result;
	}
}
