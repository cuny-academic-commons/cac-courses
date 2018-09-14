import React, { Component } from 'react'

import AutocompleteSelector from './AutocompleteSelector'

class UserSearch extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			selectedUsers: []
		}
	}

	render() {
		const { selectedUsers } = this.state
		const { handleSelectedUsersUpdate, selectedUserIds } = this.props

		const inputPlaceholder = "Start typing to find a user"

		const searchRequest = (searchTerm) => ( wp.apiFetch( {
			path: wp.url.addQueryArgs( `/cac-courses/v1/user`, { search: searchTerm } ),
		} ) );

		const searchResultsFormatCallback = (item) => item

		const populateSelectionsCallback = (callback) => {
			if ( 0 === selectedUserIds.length ) {
				callback( [] )
				return
			}

			const request = wp.apiFetch( {
				path: wp.url.addQueryArgs( `/wp/v2/users`, { include: selectedUserIds } ),
			} );

			request.then( ( foundUsers ) => {
				const newSelectedUsers = foundUsers.map( (user) => ( {
					label: user.name + ' (' + user.login + ')',
					value: user.id,
				} ) );

				callback( newSelectedUsers )
			} );
		}

		return (
			<AutocompleteSelector
				handleSelectionsUpdate={handleSelectedUsersUpdate}
				inputPlaceholder={inputPlaceholder}
				populateSelectionsCallback={populateSelectionsCallback}
				searchRequest={searchRequest}
				searchResultsFormatCallback={searchResultsFormatCallback}
				selections={selectedUsers}
			/>
		)
	}
}

export default UserSearch
