import Autocomplete from 'react-autocomplete'
import React, { Component } from 'react'

import UserSearchSelection from './UserSearchSelection'

class UserSearch extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			cached: {},
			inputValue: '',
			allUsers: [],
			selectedUsers: [],
		}

		this.handleInputChange = this.handleInputChange.bind(this)
		this.handleRemoveClick = this.handleRemoveClick.bind(this)
		this.handleSelect = this.handleSelect.bind(this)
	}

	componentDidMount() {
		const { selectedUserIds } = this.props

		const request = wp.apiFetch( {
			path: wp.url.addQueryArgs( `/wp/v2/users`, { include: selectedUserIds } ),
		} );

		request.then( ( foundUsers ) => {
			const newSelectedUsers = foundUsers.map( (user) => ( {
				label: user.name + ' (' + user.login + ')',
				value: user.id,
			} ) );

			this.setSelectedUsers( newSelectedUsers )
		} );
	}

	handleInputChange(e) {
		const newValue = e.target.value
    const inputValue = newValue.replace(/\W/g, '');

    this.setState({ inputValue });

		if ( this.state.cached.hasOwnProperty( inputValue ) ) {
			this.setState( { allUsers: this.state.cached[ inputValue ] } )
			return newValue;
		}

		const request = wp.apiFetch( {
			path: wp.url.addQueryArgs( `/cac-courses/v1/user`, { search: newValue } ),
		} );

		request.then( ( foundUsers ) => {
			this.setState( { allUsers: foundUsers } )

			let newCached = Object.assign( {}, this.state.cached )
			newCached[ newValue ] = foundUsers
			this.setState( { cached: newCached } )
		} );
  }

	handleSelect( match ) {
		this.setState( { inputValue: '' } )

		let i, matchedUser
		for ( i in this.state.allUsers ) {
			if ( match === this.state.allUsers[i].label ) {
				matchedUser = this.state.allUsers[i]
				break;
			}
		}

		if ( 'undefined' === typeof matchedUser ) {
			return
		}

		// No dupes.
		let j
		for ( j in this.state.selectedUsers ) {
			if ( match === this.state.selectedUsers[j].label ) {
				return
			}
		}

		let newSelectedUsers = [ ...this.state.selectedUsers, matchedUser ]
		this.setSelectedUsers( newSelectedUsers )
	}

	handleRemoveClick( userId ) {
		let newSelectedUsers = this.state.selectedUsers.filter( user => user.value !== userId )
		this.setSelectedUsers( newSelectedUsers )
	}

	setSelectedUsers( newSelectedUsers ) {
		this.setState( { selectedUsers: newSelectedUsers } )
		this.props.handleSelectedUsersUpdate( newSelectedUsers )
	}

	render() {
		const selectedUserEls = this.state.selectedUsers.map( (user) =>
			<UserSearchSelection
				key={'selected-user-' + user.value}
				label={user.label}
				onRemoveClick={this.handleRemoveClick}
				userId={user.value}
			/>
		)

		const autocompleteStyles = {
			borderRadius: '3px',
			boxShadow: '0 2px 12px rgba(0, 0, 0, 0.1)',
			background: 'rgba(255, 255, 255, 0.9)',
			padding: '2px 4px',
			fontSize: '90%',
			position: 'fixed',
			overflow: 'auto',
			maxHeight: '50%', // TODO: don't cheat, let it flow to the bottom
			zIndex: '100',
		}

		const inputProps = {
			placeholder: 'Start typing to find a user',
		}

		return (
			<div>
				<Autocomplete
					getItemValue={(user) => user.label}
					inputProps={inputProps}
					items={this.state.allUsers || []}
					menuStyle={autocompleteStyles}
					onChange={this.handleInputChange}
					onSelect={this.handleSelect}
					renderItem={(item, isHighlighted) =>
						<div
							style={{background: isHighlighted ? 'lightgray' : 'white'}}
							key={'user-' + item.value}>
								{item.label}
						</div>
					}
					value={this.state.inputValue}
				/>

				<ul className="selected-user-list">
					{selectedUserEls}
				</ul>
			</div>
		)
	}

	getUserMatches() {

	}
}

export default UserSearch
