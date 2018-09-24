import React, { Component } from 'react'

import AutocompleteSelector from './AutocompleteSelector'

class GroupSearch extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			selectedGroups: []
		}
	}

	render() {
		const { selectedGroups } = this.state
		const { handleSelectedGroupsUpdate, selectedGroupIds } = this.props

		const inputPlaceholder = "Start typing to find a group"

		const searchRequest = (searchTerm) => ( wp.apiFetch( {
			path: wp.url.addQueryArgs(
				`/buddypress/v1/groups`,
				{
					per_page: 25,
					search: searchTerm,
					orderby: 'name',
					order: 'asc',
				}
			),
		} ) )

		const searchResultsFormatCallback = (group) => ( {
				label: group.name + ' (' + group.slug + ')',
				value: group.id,
		} )

		const populateSelectionsCallback = (callback) => {
			if ( 0 === selectedGroupIds.length ) {
				callback( [] )
				return;
			}

			const request = wp.apiFetch( {
				path: wp.url.addQueryArgs(
					`/buddypress/v1/groups`,
					{
						per_page: 50,
						include: selectedGroupIds
					}
				),
			} );

			request.then( ( foundGroups ) => {
				const newSelectedGroups = foundGroups.map( (group) => ( {
					label: group.name + ' (' + group.slug + ')',
					value: group.id,
				} ) );

				callback( newSelectedGroups )
			} );
		}

		return (
			<AutocompleteSelector
				handleSelectionsUpdate={handleSelectedGroupsUpdate}
				inputPlaceholder={inputPlaceholder}
				populateSelectionsCallback={populateSelectionsCallback}
				searchRequest={searchRequest}
				searchResultsFormatCallback={searchResultsFormatCallback}
				selections={selectedGroups}
			/>
		)
	}
}

export default GroupSearch
