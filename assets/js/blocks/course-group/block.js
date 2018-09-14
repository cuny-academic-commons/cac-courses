/**
 * Block: course-group
 */

//  Import CSS.
//import './style.scss';
//import './editor.scss';

import GroupSearch from '../../components/GroupSearch'

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

registerBlockType( 'cac-courses/cac-course-group', {
	title: __( 'Course Group' ), // Block title.
	icon: 'smiley',
	category: 'common',
	keywords: [
		__( 'Group' ),
		__( 'Course' ),
	],

	attributes: {
		groupIds: {
			type: 'string',
			source: 'meta',
			meta: 'course-group-ids',
		},
	},

	edit: function( props ) {
		const {
			attributes: {
				groupIds
			}
		} = props

		const handleSelectedGroupsUpdate = (selectedGroups) => {
			const ids = selectedGroups.map( (group) => group.value )
			props.setAttributes( { groupIds: JSON.stringify(ids) } )
		}

		let selectedGroupIds = []
		if ( props.attributes.groupIds.length > 0 ) {
			selectedGroupIds = JSON.parse( props.attributes.groupIds )
		}

		const title = 'Group'
		const gloss = 'Select the group associated with this course'

		return (
			<div className="cac-course-group-block">
				<h2>{title}</h2>
				<p>{gloss}</p>
				<GroupSearch
					handleSelectedGroupsUpdate={handleSelectedGroupsUpdate}
					selectedGroupIds={selectedGroupIds}
				/>
			</div>
		)
	},

	save: function( props ) {
		return (
			<div>&nbsp;</div>
		);
	}
} );
