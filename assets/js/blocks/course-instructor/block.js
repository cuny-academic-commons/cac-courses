/**
 * Block: course-instructor
 */

//  Import CSS.
//import './style.scss';
import './editor.scss';

import UserSearch from '../../components/UserSearch'

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

registerBlockType( 'cac-courses/cac-course-instructor', {
	title: __( 'Course Instructor' ), // Block title.
	icon: 'book-alt',
	category: 'common',
	keywords: [
		__( 'Instructor' ),
		__( 'Faculty' ),
		__( 'Professor' ),
	],

	attributes: {
		instructorIds: {
			type: 'string',
			source: 'meta',
			meta: 'instructor-ids',
		},
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
		const {
			attributes: {
				instructorIds
			}
		} = props

		const handleSelectedUsersUpdate = (selectedUsers) => {
			const ids = selectedUsers.map( (user) => user.value )
			props.setAttributes( { instructorIds: JSON.stringify(ids) } )
		}

		const selectedInstructorIds = JSON.parse( props.attributes.instructorIds )

		const title = 'Course Instructor'
		const gloss = 'Select one or more instructors for this course'

		return (
			<div className="cac-course-instructor-block">
				<h2>{title}</h2>
				<p>{gloss}</p>
				<UserSearch
					handleSelectedUsersUpdate={handleSelectedUsersUpdate}
					selectedUserIds={selectedInstructorIds}
				/>
			</div>
		)
		const {
			attributes: {
				demoSiteId,
				selectedDemoSites,
				selectedTemplateSites,
				templateSiteId
			}
		} = props

		const setSelectedTemplateSites = (selectedTemplateSites) => {
			props.setAttributes( { selectedTemplateSites } )
		}

		const setSelectedTemplateSiteId = (selectedTemplateSiteId) => {
			props.setAttributes( { templateSiteId: selectedTemplateSiteId } )
		}

		const templateSiteField = <SiteSearch
			labelText="Template Site"
			setSelectedSites={setSelectedTemplateSites}
			setSelectedSiteId={setSelectedTemplateSiteId}
			selected={selectedTemplateSites}
			selectedSiteId={templateSiteId}
		/>

		const setSelectedDemoSites = (selectedDemoSites) => {
			props.setAttributes( { selectedDemoSites } )
		}

		const setSelectedDemoSiteId = (selectedDemoSiteId) => {
			props.setAttributes( { demoSiteId: selectedDemoSiteId } )
		}

		const demoSiteField = <SiteSearch
			labelText="Demo Site"
			setSelectedSites={setSelectedDemoSites}
			setSelectedSiteId={setSelectedDemoSiteId}
			selected={selectedDemoSites}
			selectedSiteId={demoSiteId}
		/>

		return (
			<div>
				{templateSiteField}
				{demoSiteField}
			</div>
		)
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return (
			<div>&nbsp;</div>
		);
	}
} );
