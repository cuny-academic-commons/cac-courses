import React, { Component } from 'react'

class UserSearchSelection extends React.Component {
	render() {
		const { label, onRemoveClick, userId } = this.props

		return (
			<li
			  key={'selected-user-' + userId}
			>
				<a
					className="selected-user-remove"
					href="#"
					onClick={() => {onRemoveClick(userId)}}
				>&times;</a>&nbsp;<span className="selected-user-name">{label}</span>
			</li>
		)
	}
}

export default UserSearchSelection
