/**
 * Block: course-instructor
 */

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

import Select from 'react-select'

registerBlockType( 'cac-courses/cac-course-campus', {
	title: __( 'Course Campus' ), // Block title.
	icon: 'welcome-learn-more',
	category: 'common',
	keywords: [
		__( 'School' ),
		__( 'Campus' ),
		__( 'College' ),
	],

	attributes: {
		campusSlugs: {
			type: 'string',
			source: 'meta',
			meta: 'campus-slugs',
		},
	},

	edit: function( props ) {
		const {
			attributes: {
				campusSlugs
			}
		} = props

		const title = 'Campus'
		const gloss = 'Select one or more campuses to associate with this course'
		const placeholder = 'Select one or more campuses'

		const campusData = CACCourses.campuses

		const handleChange = (selectedCampuses) => {
			const campusSlugs = selectedCampuses.map( (campus) => campus.value )
			props.setAttributes( { campusSlugs: JSON.stringify(campusSlugs) } )
		}

		let defaultValue = []
		const selectedSlugs = props.attributes.campusSlugs
		if ( campusSlugs.length > 0 ) {
			defaultValue = JSON.parse( selectedSlugs ).map( (campus) => {
				for ( var theCampus of campusData ) {
					if ( theCampus.value === campus ) {
						return theCampus
					}
				}
			} )
		}

		return (
			<div className="cac-course-campus-block">
				<h2>{title}</h2>
				<p>{gloss}</p>

				<Select
					defaultValue={defaultValue}
					isMulti
					onChange={handleChange}
					options={campusData}
					placeholder={placeholder}
				/>
			</div>
		)
	},

	save: function() {}
} );
