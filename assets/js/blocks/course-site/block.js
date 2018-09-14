/**
 * Block: course-site
 */

import SiteSearch from '../../components/SiteSearch'

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

registerBlockType( 'cac-courses/cac-course-site', {
	title: __( 'Course Site' ), // Block title.
	icon: 'admin-site',
	category: 'common',
	keywords: [
		__( 'Site' ),
		__( 'Blog' ),
		__( 'Course' ),
	],

	attributes: {
		siteIds: {
			type: 'string',
			source: 'meta',
			meta: 'course-site-ids',
		},
	},

	edit: function( props ) {
		const {
			attributes: {
				siteIds
			}
		} = props

		const handleSelectedSitesUpdate = (selectedSites) => {
			const ids = selectedSites.map( (group) => group.value )
			props.setAttributes( { siteIds: JSON.stringify(ids) } )
		}

		let selectedSiteIds = []
		if ( props.attributes.siteIds.length > 0 ) {
			selectedSiteIds = JSON.parse( props.attributes.siteIds )
		}

		const title = 'Site'
		const gloss = 'Select the site associated with this course'

		return (
			<div className="cac-course-site-block">
				<h2>{title}</h2>
				<p>{gloss}</p>
				<SiteSearch
					handleSelectedSitesUpdate={handleSelectedSitesUpdate}
					selectedSiteIds={selectedSiteIds}
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
