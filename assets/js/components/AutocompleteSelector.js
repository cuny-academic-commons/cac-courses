import Autocomplete from 'react-autocomplete'
import React, { Component } from 'react'
import SearchSelection from './SearchSelection'

import { debounce } from 'throttle-debounce'

import '../../scss/AutocompleteSelector.scss';

class AutocompleteSelector extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			cached: {},
			inputValue: '',
			allItems: [],
			selectedItems: [],
			selections: [],
		}

		this.searchThrottled = debounce( 200, this.performQuery )

		this.handleInputChange = this.handleInputChange.bind(this)
		this.handleRemoveClick = this.handleRemoveClick.bind(this)
		this.handleSelect = this.handleSelect.bind(this)
	}

	componentDidMount() {
		const { populateSelectionsCallback } = this.props

		const selections = populateSelectionsCallback( (selections) => {
			this.setSelections( selections )
		} )
	}

	performQuery(searchTerm) {
		const { searchRequest, searchResultsFormatCallback } = this.props

		const request = searchRequest( searchTerm )

		request.then( ( foundItems ) => {
			const formattedFoundItems = foundItems.map( searchResultsFormatCallback )

			this.setState( { allItems: formattedFoundItems } )

			let newCached = Object.assign( {}, this.state.cached )
			newCached[ searchTerm ] = formattedFoundItems
			this.setState( { cached: newCached } )
		} );

	}

	handleInputChange(e) {
		const newValue = e.target.value
    const inputValue = newValue;

    this.setState({ inputValue });

		if ( this.state.cached.hasOwnProperty( inputValue ) ) {
			this.setState( { allItems: this.state.cached[ inputValue ] } )
			return newValue;
		}

		this.searchThrottled( inputValue )
  }

	handleSelect( match ) {
		this.setState( { inputValue: '' } )

		let i, matchedItem
		for ( i in this.state.allItems ) {
			if ( match === this.state.allItems[i].label ) {
				matchedItem = this.state.allItems[i]
				break;
			}
		}

		if ( 'undefined' === typeof matchedItem ) {
			return
		}

		// No dupes.
		let j
		for ( j in this.state.selectedItems ) {
			if ( match === this.state.selectedItems[j].label ) {
				return
			}
		}

		let newSelections = [ ...this.state.selections, matchedItem ]
		this.setSelections( newSelections )
	}

	handleRemoveClick( itemId ) {
		let newSelections = this.state.selections.filter( item => item.value !== itemId )
		this.setSelections( newSelections )
	}

	setSelections( newSelections ) {
		const { handleSelectionsUpdate } = this.props

		this.setState( { selections: newSelections } )
		handleSelectionsUpdate( newSelections )
	}

	render() {
		const { inputPlaceholder } = this.props
		const { selections } = this.state

		const selectionEls = selections.map( (selection) =>
			<SearchSelection
				key={'selected-item-' + selection.value}
				label={selection.label}
				onRemoveClick={this.handleRemoveClick}
				itemId={selection.value}
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
			maxHeight: '50%',
			zIndex: '100',
		}

		const inputProps = {
			placeholder: inputPlaceholder,
		}

		const wrapperProps = {
			className: 'cac-autocomplete-selector-wrapper',
		}

		const wrapperStyle = {
			display: 'block',
		}

		return (
			<div>
				<Autocomplete
					getItemValue={(item) => item.label}
					inputProps={inputProps}
					items={this.state.allItems || []}
					menuStyle={autocompleteStyles}
					onChange={this.handleInputChange}
					onSelect={this.handleSelect}
					renderItem={(item, isHighlighted) =>
						<div
							style={{background: isHighlighted ? 'lightgray' : 'white'}}
							key={'item-' + item.value}>
								{item.label}
						</div>
					}
					value={this.state.inputValue}
					wrapperProps={wrapperProps}
					wrapperStyle={wrapperStyle}
				/>

				<ul className="selected-item-list">
					{selectionEls}
				</ul>
			</div>
		)
	}
}

export default AutocompleteSelector
