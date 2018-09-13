<?php

namespace CAC\Courses;

class API {
	protected $endpoints = array();

	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'init_endpoints' ) );

		add_action( 'rest_api_init', [ __CLASS__, 'add_fields_to_user_meta' ] );
	}

	public static function init_endpoints() {
		$endpoints['user'] = new Endpoints\User();

		foreach ( $endpoints as $endpoint ) {
			$endpoint->register_routes();
		}
	}

	public static function add_fields_to_user_meta() {
		if ( ! current_user_can( 'manage_users' ) ) {
			return;
		}

		register_rest_field(
			'user',
			'login',
			[
				'get_callback' => function( $user ) {
					return $user['username'];
				}
			]
		);
	}
}
