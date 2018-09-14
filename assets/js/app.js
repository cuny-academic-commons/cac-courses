/**
 * Gutenberg Blocks
 *
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this dir and include code
 * for that block here as well.
 *
 * All blocks should be included here since this is the file that
 * Webpack is compiling as the input file.
 */

(function($){
	$(document).ready(function(){
		if ( ! wp.hasOwnProperty( 'components' ) ) {
			return;
		}

		const { registerPlugin } = wp.plugins
		const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost
		const { PanelBody } = wp.components
		const { Fragment } = wp.element

		const CoursesSidebar = () => (
			<Fragment>
				<PluginSidebarMoreMenuItem
					target="cac-courses-sidebar"
					icon="smiley"
				>
					Course Data
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="cac-courses-sidebar"
					title="Course Data"
					icon="smiley"
				>
					<PanelBody>
						Foo
					</PanelBody>
				</PluginSidebar>
			</Fragment>
		)

		registerPlugin(
			'cac-courses',
			{
				render: CoursesSidebar
			}
		);
	});
}(jQuery));

import './blocks/course-instructor/block.js';
import './blocks/course-campus/block.js';
import './blocks/course-group/block.js';
import './blocks/course-site/block.js';
