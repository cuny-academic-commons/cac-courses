<?php

namespace CAC\Courses;

class Featured {
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
	}

	public static function admin_menu() {
		if ( isset( $_POST['cac-courses-featured-nonce'] ) && current_user_can( 'manage_options' ) ) {
			check_admin_referer( 'cac-courses-featured', 'cac-courses-featured-nonce' );

			$ids = wp_unslash( $_POST['course-ids'] );
			$ids = explode( "\n", $ids );
			$ids = array_filter( $ids );
			$ids = array_map( 'intval', $ids );

			update_option( 'featured_course_ids', $ids );

			wp_safe_redirect( admin_url( 'edit.php?post_type=cac_course&page=cac-courses-featured&saved=1' ) );
			die;
		}

		add_submenu_page(
			'edit.php?post_type=cac_course',
			'Featured Courses',
			'Featured',
			'manage_options',
			'cac-courses-featured',
			[ __CLASS__, 'admin' ]
		);
	}

	public static function get_course_ids() {
		$ids = get_option( 'featured_course_ids' );
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}
		return array_map( 'intval', $ids );
	}

	public static function admin() {
		$ids = self::get_course_ids();

		?>
		<div class="wrap">
			<h2>Featured Courses</h2>

			<form method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="course-ids">Course IDs</label>
							</th>

							<td>
								<textarea style="height:100px" class="widefat" id="course-ids" name="course-ids"><?php echo esc_textarea( implode( "\n", $ids ) ); ?></textarea>
								<p class="description">One per line</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php wp_nonce_field( 'cac-courses-featured', 'cac-courses-featured-nonce' ); ?>

				<input type="submit" class="button-primary" value="Save Changes" />
			</form>
		</div>
		<?php
	}
}
