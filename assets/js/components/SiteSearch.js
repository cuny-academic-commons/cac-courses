import React, { Component } from 'react'

import AutocompleteSelector from './AutocompleteSelector'

class SiteSearch extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			selectedSites: []
		}
	}

	render() {
		const { selectedSites } = this.state
		const { handleSelectedSitesUpdate, selectedSiteIds } = this.props

		const inputPlaceholder = "Start typing to find a site"

		const searchRequest = (searchTerm) => ( wp.apiFetch( {
			path: wp.url.addQueryArgs(
				`/cac/v1/site`,
				{
					search: searchTerm,
				}
			),
		} ) )

		const searchResultsFormatCallback = (site) => ( {
			label: site.name + ' (' + site.url + ')',
			value: site.id,
		} )

		// todo
		const populateSelectionsCallback = (callback) => {
			if ( 0 === selectedSiteIds.length ) {
				callback( [] )
				return;
			}

			const request = wp.apiFetch( {
				path: wp.url.addQueryArgs(
					`/cac/v1/site`,
					{
						include: selectedSiteIds
					}
				),
			} );

			request.then( ( foundSites ) => {
				const newSelectedSites = foundSites.map( (site) => ( {
					label: site.name + ' (' + site.url + ')',
					value: site.id,
				} ) );

				callback( newSelectedSites )
			} );
		}

		return (
			<AutocompleteSelector
				handleSelectionsUpdate={handleSelectedSitesUpdate}
				inputPlaceholder={inputPlaceholder}
				populateSelectionsCallback={populateSelectionsCallback}
				searchRequest={searchRequest}
				searchResultsFormatCallback={searchResultsFormatCallback}
				selections={selectedSites}
			/>
		)
	}
}

export default SiteSearch
