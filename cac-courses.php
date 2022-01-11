<?php
/**
 * Plugin Name: CAC Courses
 * Description: Courses functionality for the CUNY Academic Commons
 * Plugin URI: https://commons.gc.cuny.edu
 * Author: The CUNY Academic Commons
 * Author URI: https://commons.gc.cuny.edu
 * Version: 1.0.0
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: true
 *
 * @package CACCourses
 */

/**
 * Requires:
 *
 * - BP-REST
 * - cac-endpoints
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAC_COURSES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAC_COURSES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require __DIR__ . '/autoload.php';

/**
 * Shorthand function to fetch our CAC Courses instance.
 *
 * @since 0.1.0
 */
function cac_courses() {
	return \CAC\Courses\App::get_instance();
}

add_action( 'plugins_loaded', function() {
	$cc = cac_courses();
	$cc::init();
} );
