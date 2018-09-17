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
		add_action( 'added_post_meta', [ __CLASS__, 'sync_post_meta_and_tax_terms' ], 10, 4 );

		add_action( 'init', function() {
			add_shortcode( 'cac-courses', [ __CLASS__, 'render_shortcode' ] );
		} );

		// Frontend template integration.
//		Frontend::init();
	}

	public static function register_post_type() {
		register_post_type(
			self::$post_type,
			[
				'public'       => true,
				'rewrite'      => [
					'slug'       => 'courses',
					'with_front' => false,
				],
				'has_archive'  => true,
				'show_ui'      => current_user_can( 'activate_plugins' ),
				'show_in_rest' => true,
				'template'     => [
					[
						'core/paragraph',
						[
							'placeholder' => 'Enter description',
						]
					],
					[
						'cac-courses/cac-course-instructor'
					],
					[
						'cac-courses/cac-course-group'
					],
					[
						'cac-courses/cac-course-site'
					],
					[
						// At the end of the list so failures don't break other items.
						'cac-courses/cac-course-campus'
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
				'show_in_rest' => false,
				'show_ui'      => false, // @todo
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
				'show_in_rest' => false,
				'show_ui'      => false, // @todo
				'public'       => false,
			]
		);

		register_taxonomy(
			'cac_course_group',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Groups', 'cac-courses' ),
					'singular_name' => __( 'Group', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Group', 'cac-courses' ),
				],
				'show_in_rest' => false,
				'show_ui'      => false,
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

		register_taxonomy(
			'cac_course_site',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Sites', 'cac-courses' ),
					'singular_name' => __( 'Site', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Site', 'cac-courses' ),
				],
				'show_in_rest' => false,
				'show_ui'      => false,
				'public'       => false,
			]
		);

		register_meta(
			'post',
			'course-group-ids',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
			]
		);

		register_meta(
			'post',
			'course-site-ids',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
			]
		);

		/*
		 * Saves sometimes appear to fail because of https://core.trac.wordpress.org/ticket/42069
		 * We register this last so that failures don't cause the whole thing to bail
		 */
		register_meta(
			'post',
			'campus-slugs',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
			]
		);
	}

	public static function meta_tax_map() {
		return [
			'instructor-ids' => [
				'taxonomy'    => 'cac_course_instructor',
				'term_prefix' => 'instructor_',
			],
			'campus-slugs' => [
				'taxonomy'    => 'cac_course_campus',
				'term_prefix' => '',
			],
			'course-group-ids' => [
				'taxonomy'    => 'cac_course_group',
				'term_prefix' => 'group_',
			],
			'course-site-ids' => [
				'taxonomy'    => 'cac_course_site',
				'term_prefix' => 'site_',
			],
		];
	}

	public static function sync_post_meta_and_tax_terms( $meta_id, $object_id, $meta_key, $meta_value ) {
		$map = self::meta_tax_map();

		if ( ! isset( $map[ $meta_key ] ) ) {
			return;
		}

		$taxonomy    = $map[ $meta_key ]['taxonomy'];
		$term_prefix = $map[ $meta_key ]['term_prefix'];

		$meta_values = json_decode( $meta_value );

		$meta_terms = array_map(
			function( $mv ) use ( $term_prefix ) {
				return $term_prefix . $mv;
			},
			$meta_values
		);

		wp_set_post_terms( $object_id, $meta_terms, $taxonomy );
	}
}
