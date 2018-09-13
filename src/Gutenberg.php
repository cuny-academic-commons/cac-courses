<?php

namespace CAC\Courses;

class Gutenberg {
	public static function init() {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
//		add_action( 'enqueue_block_assets', [ __CLASS__, 'enqueue_block_assets' ] );
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
