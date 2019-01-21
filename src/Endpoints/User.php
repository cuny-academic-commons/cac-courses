<?php

namespace CAC\Courses\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

use \BP_User_Query;

/**
 * user endpoint.
 */
class User extends WP_REST_Controller {
	/**
	 * Register endpoint routes.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'cac-courses/v' . $version;

		register_rest_route(
			$namespace,
			'/user',
			array(
				array(
					'methods'         => WP_REST_Server::READABLE,
					'callback'        => array( $this, 'search' ),
					'permission_callback' => array( $this, 'search_permissions_check' ),
					'args'            => $this->get_endpoint_args_for_item_schema( true ),
				),
			)
		);
	}

	/**
	 * Permission check for searching.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function search_permissions_check( $request ) {
		return current_user_can( 'edit_cac_courses' );
	}

	/**
	 * Performs a search.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function search( $request ) {
		global $wpdb;

		$search_term = $request->get_param( 'search' );

		$results = array();
		if ( $search_term ) {
			$bp = buddypress();

			$like    = '%' . $wpdb->esc_like( $search_term ) . '%';
			$matches = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} u WHERE ( u.user_email LIKE %s ) OR ( u.user_login LIKE %s ) OR u.ID in ( SELECT user_id FROM {$bp->profile->table_name_data} WHERE field_id = 1 AND value LIKE %s ) LIMIT 25", $like, $like, $like ) );

			if ( ! $matches ) {
				return $results;
			}

			$user_query = new BP_User_Query( [
				'include'  => $matches,
				'per_page' => 20,
				'type'     => 'alphabetical',
			] );

			if ( $user_query->results ) {
				$results = array_map( [ $this, 'format_user' ], $user_query->results );
			}
		}

		// Reset indexes so that JSON response is an array, not an object.
		$results = array_values( $results );

		$response = rest_ensure_response( $results );

		return $response;
	}

	protected function format_user( $user ) {
		return [
			'value' => $user->ID,
			'label' => sprintf( '%s (%s)', $user->fullname, $user->user_login ),
		];
	}
}
