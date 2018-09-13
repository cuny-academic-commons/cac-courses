<?php

namespace CAC\Courses;

class App {
	protected static $post_type = 'cac_course';

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return CAC\Courses\App
	 */
	private function __construct() {
		return $this;
	}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public static function init() {
		// Initialize Gutenberg integration.
		Gutenberg::init();

		// Schema.
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );

		// API endpoints.
		API::init();

		// Tax/meta sync.
		add_action( 'updated_post_meta', [ __CLASS__, 'sync_post_meta_and_tax_terms' ], 10, 4 );

		// Frontend template integration.
//		Frontend::init();
	}

	public static function register_post_type() {
		register_post_type(
			self::$post_type,
			[
				'public'       => false,
				'show_ui'      => current_user_can( 'activate_plugins' ),
				'show_in_rest' => true,
				'template'     => [
					[
						'cac-courses/cac-course-instructor'
					],
					[
						'core/paragraph',
						[
							'placeholder' => 'Enter description',
						]
					],
				],
				'labels'       => [
					'name'          => 'Courses',
					'singular_name' => 'Course',
				],
				'supports'     => [
					'custom-fields',
					'editor',
					'page-attributes',
					'thumbnail',
					'title',
				],
			]
		);

		register_taxonomy(
			'cac_course_campus',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Campuses', 'cac-courses' ),
					'singular_name' => __( 'Campus', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Campus', 'cac-courses' ),
				],
				'show_in_rest' => true,
				'show_ui'      => true, // @todo
				'public'       => false,
			]
		);

		register_taxonomy(
			'cac_course_instructor',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Instructors', 'cac-courses' ),
					'singular_name' => __( 'Instructor', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Instructor', 'cac-courses' ),
				],
				'show_in_rest' => true,
				'show_ui'      => false, // @todo
				'public'       => false,
			]
		);

		register_meta(
			'post',
			'instructor-ids',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
			]
		);

		register_meta(
			'post',
			'course-site-id',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'integer',
			]
		);

		register_meta(
			'post',
			'course-group-id',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'integer',
			]
		);
	}

	public static function sync_post_meta_and_tax_terms( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'instructor-ids' !== $meta_key ) {
			return;
		}

		$instructor_ids = json_decode( $meta_value );

		$instructor_terms = array_map(
			function( $user_id ) {
				return 'instructor_' . $user_id;
			},
			$instructor_ids
		);

		wp_set_post_terms( $object_id, $instructor_terms, 'cac_course_instructor' );
	}
}
