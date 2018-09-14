import React, { Component } from 'react'

class SearchSelection extends React.Component {
	render() {
		const { label, onRemoveClick, itemId } = this.props

		return (
			<li
			  key={'selected-item-' + itemId}
			>
				<a
					className="selected-item-remove"
					href="#"
					onClick={() => {onRemoveClick(itemId)}}
				>&times;</a>&nbsp;<span className="selected-item-name">{label}</span>
			</li>
		)
	}
}

export default SearchSelection
