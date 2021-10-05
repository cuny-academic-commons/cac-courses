<?php

namespace CAC\Courses;

use \WP_Site;

class Course {
	protected $data = [
		'id' => null,
		'title' => '',
		'group_ids' => null,
		'site_ids' => null,
		'instructor_ids' => null,
		'disciplinary_clusters' => null,
		'campuses' => null,
		'terms' => null,
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

	public function get_terms() {
		if ( null !== $this->data['terms'] ) {
			return $this->data['terms'];
		}

		$terms = get_post_meta( $this->get_id(), 'course-terms', true );

		if ( ! $terms ) {
			$terms = [];
		} else {
			$terms = json_decode( $terms );
		}

		$this->data['terms'] = $terms;

		return $this->data['terms'];
	}

	public function get_campuses() {
		if ( null !== $this->data['campuses'] ) {
			return $this->data['campuses'];
		}

		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_campus' );

		$this->data['campuses'] = wp_list_pluck( $terms, 'name' );

		return $this->data['campuses'];
	}

	public function get_disciplinary_clusters() {
		if ( null !== $this->data['disciplinary_clusters'] ) {
			return $this->data['disciplinary_clusters'];
		}

		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_disciplinary_cluster' );

		$this->data['disciplinary_clusters'] = wp_list_pluck( $terms, 'slug' );

		return $this->data['disciplinary_clusters'];
	}

	public function get_instructor_ids() {
		if ( null !== $this->data['instructor_ids'] ) {
			return $this->data['instructor_ids'];
		}

		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_instructor' );
		$map   = App::meta_tax_map();

		$instructor_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['instructor-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		$this->data['instructor_ids'] = array_filter( $instructor_ids );

		return $this->data['instructor_ids'];
	}

	public function get_group_ids() {
		if ( null !== $this->data['group_ids'] ) {
			return $this->data['group_ids'];
		}

		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_group' );
		$map   = App::meta_tax_map();

		$group_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['course-group-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		$this->data['group_ids'] = array_filter( $group_ids );

		return $this->data['group_ids'];
	}

	public function get_site_ids() {
		if ( null !== $this->data['site_ids'] ) {
			return $this->data['site_ids'];
		}

		$terms = wp_get_post_terms( $this->get_id(), 'cac_course_site' );
		$map   = App::meta_tax_map();

		$site_ids = array_map(
			function( $term ) {
				$prefix_length = strlen( $this->meta_tax_map['course-site-ids']['term_prefix'] );
				return (int) substr( $term->name, $prefix_length );
			},
			$terms
		);

		$this->data['site_ids'] = array_filter( $site_ids );

		return $this->data['site_ids'];
	}

	public function get_campus_names() {
		$slugs = $this->get_campuses();

		$campuses_data = cac_get_cuny_campuses();

		$retval = array_map(
			function( $slug ) use ( $campuses_data ) {
				if ( 'cuny-wide' === $slug ) {
					return 'CUNY-wide';
				} else {
					return $campuses_data[ $slug ]['full_name'] ?: '';
				}
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
				if ( ! ( $site instanceof WP_Site ) ) {
					return '';
				}

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

	public function set_id( $id ) {
		$this->data['id'] = (int) $id;
	}

	public function set_title( $title ) {
		$this->data['title'] = $title;
	}

	public function set_group_ids( $group_ids ) {
		$this->data['group_ids'] = array_map( 'intval', $group_ids );
	}

	public function set_site_ids( $site_ids ) {
		$this->data['site_ids'] = array_map( 'intval', $site_ids );
	}

	public function set_instructor_ids( $instructor_ids ) {
		$this->data['instructor_ids'] = array_map( 'intval', $instructor_ids );
	}

	public function set_campuses( $campuses ) {
		$this->data['campuses'] = $campuses;
	}

	public function set_disciplinary_clusters( $disciplinary_clusters ) {
		$this->data['disciplinary_clusters'] = $disciplinary_clusters;
	}

	public function set_terms( $terms ) {
		$this->data['terms'] = $terms;
	}

	public function save() {
		if ( $this->data['id'] ) {
			$post_id = $this->get_id();
		} else {
			$post_data = [
				'post_type'   => 'cac_course',
				'post_status' => 'publish',
				'post_title'  => $this->get_title(),
			];

			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			$this->set_id( $post_id );
		}

		// Save to meta and let the sync mechanism mirror to taxonomy.
		update_post_meta( $post_id, 'course-group-ids', json_encode( $this->get_group_ids() ) );
		update_post_meta( $post_id, 'course-site-ids', json_encode( $this->get_site_ids() ) );
		update_post_meta( $post_id, 'instructor-ids', json_encode( $this->get_instructor_ids() ) );
		update_post_meta( $post_id, 'campus-slugs', json_encode( $this->get_campuses() ) );
		update_post_meta( $post_id, 'course-terms', json_encode( $this->get_terms() ) );

		wp_set_object_terms( $post_id, $this->get_disciplinary_clusters(), 'cac_course_disciplinary_cluster' );

		$this->update_public_flag();
	}

	public function update_public_flag() {
		$has_public_group_or_site = false;
		foreach ( $this->get_site_ids() as $site_id ) {
			$blog_public = (int) get_blog_option( $site_id, 'blog_public' );
			if ( 1 === $blog_public || 0 === $blog_public ) {
				$has_public_group_or_site = true;
				break;
			}
		}

		if ( ! $has_public_group_or_site ) {
			foreach ( $this->get_group_ids() as $group_id ) {
				$group = groups_get_group( $group_id );
				if ( 'public' === $group->status ) {
					$has_public_group_or_site = true;
					break;
				}
			}
		}

		if ( $has_public_group_or_site ) {
			update_post_meta( $this->get_id(), 'has-public-group-or-site', 1 );
		} else {
			delete_post_meta( $this->get_id(), 'has-public-group-or-site' );
		}
	}
}
