<?php

namespace CAC\Courses;

class Course {
	protected $data = [
		'title' => '',
	];

	protected $meta_tax_map;

	public function __construct( $post_id = null ) {
		if ( $post_id ) {
			$this->populate( $post_id );
		}

		$this->meta_tax_map = App::meta_tax_map();
	}

	protected function populate( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'cac_course' !== $post->post_type ) {
			return;
		}

		$this->data['id']    = $post->ID;
		$this->data['title'] = $post->post_title;
	}

	public function get_id() {
		return (int) $this->data['id'];
	}

	public function get_title() {
		return $this->data['title'];
	}

	public function get_campuses() {
		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_campus' );
		return wp_list_pluck( $terms, 'name' );
	}

	public function get_instructor_ids() {
		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_instructor' );
		$map   = App::meta_tax_map();

		$instructor_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['instructor-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		return array_filter( $instructor_ids );
	}

	public function get_group_ids() {
		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_group' );
		$map   = App::meta_tax_map();

		$group_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['course-group-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		return array_filter( $group_ids );
	}

	public function get_site_ids() {
		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_site' );
		$map   = App::meta_tax_map();

		$site_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['course-site-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		return array_filter( $site_ids );
	}

	public function get_campus_names() {
		$slugs = $this->get_campuses();

		$campuses_data = cac_get_cuny_campuses();

		$retval = array_map(
			function( $slug ) use ( $campuses_data ) {
				return $campuses_data[ $slug ]['full_name'] ?: '';
			},
			$slugs
		);

		return array_filter( $retval );
	}

	public function get_instructor_links() {
		return array_map( 'bp_core_get_userlink', $this->get_instructor_ids() );
	}

	public function get_groups() {
		return array_map( 'groups_get_group', $this->get_group_ids() );
	}

	public function get_sites() {
		return array_map( 'get_site', $this->get_site_ids() );
	}

	public function get_group_links() {
		$groups = array_filter(
			$this->get_groups(),
			function( $group ) {
				return 'hidden' !== $group->status;
			}
		);

		return array_map(
			function( $group ) {
				return sprintf(
					'<a href="%s">%s</a> (%s)',
					esc_attr( bp_get_group_permalink( $group ) ),
					esc_html( bp_get_group_name( $group ) ),
					'public' === $group->status ? 'Public Group' : 'Private Group'
				);
			},
			$groups
		);
	}

	public function get_site_links() {
		return array_map(
			function( $site ) {
				return sprintf(
					'<a href="%s">%s</a> (%s)',
					$site->home,
					$site->blogname,
					0 <= $site->public ? 'Public Site' : 'Private Site'
				);
			},
			$this->get_sites()
		);
	}
}
