<?php

namespace CAC\Courses;

class Gutenberg {
	public static function init() {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
	}

	public static function admin_enqueue_scripts() {
		global $pagenow, $post_type;

		if ( empty( $post_type ) || 'cac_course' !== $post_type ) {
			return;
		}

		wp_enqueue_script(
			'cac-courses-gutenberg-js',
			CAC_COURSES_PLUGIN_URL . '/dist/app.build.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'jquery' )
		);

		$campuses_data = [
			[
				'value' => 'cuny-wide',
				'label' => 'CUNY-wide',
			],
		];
		foreach ( cac_get_cuny_campuses() as $campus_slug => $campus_data ) {
			$campuses_data[] = [
				'value' => $campus_slug,
				'label' => $campus_data['short_name'],
			];
		}

		wp_localize_script(
			'cac-courses-gutenberg-js',
			'CACCourses',
			[
				'campuses' => $campuses_data,
			]
		);
	}

	public static function enqueue_block_assets() {
		/*
		// Styles.
		wp_enqueue_style(
			$block . '-style',
			CAC_SITE_TEMPLATES_PLUGIN_URL . '/dist/blocks.style.build.css',
			array( 'wp-blocks' ) // Dependency to include the CSS after it.
		);
		*/
	}
}
