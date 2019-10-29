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
		add_action( 'updated_post_meta', [ __CLASS__, 'sync_course_term_to_sortable_meta' ], 10, 4 );
		add_action( 'added_post_meta', [ __CLASS__, 'sync_course_term_to_sortable_meta' ], 10, 4 );

		add_action( 'admin_init', [ __CLASS__, 'add_role_caps' ], 99 );

		add_action( 'init', function() {
			add_shortcode( 'cac-courses', [ __CLASS__, 'render_shortcode' ] );
		} );

		add_action( 'pre_get_posts', function( $r ) {
			if ( ! $r->is_post_type_archive( 'cac_course' ) ) {
				return;
			}

			if ( ! $r->is_main_query() ) {
				return;
			}

			$r->set( 'meta_key', 'course-term-sortable' );

			$r->set( 'orderby', [
				'meta_value' => 'DESC',
				'post_title' => 'ASC',
			] );
		} );
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
				'capability_type' => 'cac_course',
				'map_meta_cap' => true,
				'has_archive'  => true,
				'show_ui'      => current_user_can( 'edit_cac_courses' ),
				'show_in_rest' => true,
				'template'     => [
					[
						'core/paragraph',
						[
							'placeholder' => 'Enter description',
						]
					],
					[
						'cac-courses/cac-course-term',
					],
					[
						'cac-courses/cac-course-instructor',
					],
					[
						'cac-courses/cac-course-group',
					],
					[
						'cac-courses/cac-course-site',
					],
					[
						// At the end of the list so failures don't break other items.
						'cac-courses/cac-course-campus',
					],
				],
				'template_lock' => 'all',
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

		register_meta(
			'post',
			'course-terms',
			[
				'object_subtype' => 'cac_course',
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string',
			]
		);

		register_taxonomy(
			'cac_course_term',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Academic Terms', 'cac-courses' ),
					'singular_name' => __( 'Academic Term', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Term', 'cac-courses' ),
				],
				'show_in_rest' => false,
				'show_ui'      => false,
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

		register_taxonomy(
			'cac_course_disciplinary_cluster',
			'cac_course',
			[
				'labels' => [
					'name'          => __( 'Disciplinary Clusters', 'cac-courses' ),
					'singular_name' => __( 'Disciplinary Cluster', 'cac-courses' ),
					'add_new_term'  => __( 'Add New Disciplinary Cluster', 'cac-courses' ),
				],
				'show_in_rest' => true,
				'public'       => false,
				'show_ui'      => true,
				'hierarchical' => true, // to get checkboxes
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
			'course-terms' => [
				'taxonomy'    => 'cac_course_term',
				'term_prefix' => '',
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

	public static function sync_course_term_to_sortable_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'course-terms' !== $meta_key ) {
			return;
		}

		$course_terms = json_decode( $meta_value );
		if ( $course_terms ) {
			sort( $course_terms );

			$sort_by_term = end( $course_terms );

			if ( ! $sort_by_term ) {
				$sort_by_term = '2015-01'; // Put it last.
			}
		}

		update_post_meta( $object_id, 'course-term-sortable', $sort_by_term );
	}

	public static function add_role_caps() {
		// Add the roles you'd like to administer the custom post types
		$roles = array( 'courses_editor','editor','administrator' );

		// Loop through each role and assign capabilities
		foreach( $roles as $the_role ) {
			$role = get_role( $the_role );

			if ( ! $role ) {
				// In case of switched blog, avoid fatals.
				continue;
			}

			$role->add_cap( 'read' );
			$role->add_cap( 'read_cac_course');
			$role->add_cap( 'read_private_cac_courses' );
			$role->add_cap( 'edit_cac_course' );
			$role->add_cap( 'edit_cac_courses' );
			$role->add_cap( 'edit_others_cac_courses' );
			$role->add_cap( 'edit_published_cac_courses' );
			$role->add_cap( 'publish_cac_courses' );
			$role->add_cap( 'delete_others_cac_courses' );
			$role->add_cap( 'delete_private_cac_courses' );
			$role->add_cap( 'delete_published_cac_courses' );
		}
	}
}
