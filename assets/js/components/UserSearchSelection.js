import React, { Component } from 'react'

class UserSearchSelection extends React.Component {
	render() {
		const { label, onRemoveClick, userId } = this.props

		return (
			<li
			  key={'selected-user-' + userId}
			>
				{label}&nbsp;
				<a
					href="#"
					onClick={() => {onRemoveClick(userId)}}
				>x</a>
			</li>
		)
	}
}

export default UserSearchSelection
