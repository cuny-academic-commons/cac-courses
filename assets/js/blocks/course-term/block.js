/**
 * Block: course-term
 */

//  Import CSS.
//import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

import Select from 'react-select'

registerBlockType( 'cac-courses/cac-course-term', {
	title: __( 'Academic Term' ), // Block title.
	icon: 'calendar',
	category: 'common',
	keywords: [
		__( 'Semester' ),
		__( 'Year' ),
	],

	attributes: {
		terms: {
			type: 'string',
			source: 'meta',
			meta: 'course-terms',
		},
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
		const {
			attributes: {
				terms
			}
		} = props

		const handleChange = (selectedTerms) => {
			const terms = selectedTerms.map( (term) => term.value )
			props.setAttributes( { terms: JSON.stringify( terms ) } )
		}

		const getTermName = function ( year, semester ) {
			switch ( semester ) {
				case 1 :
					return 'Winter ' + year

				case 2 :
					return 'Spring ' + year

				case 3 :
					return 'Summer ' + year

				case 4 :
					return 'Fall ' + year
			}
		}


		const title = 'Academic Term'
		const gloss = 'Select the academic term(s) this course is associated with'
		const placeholder = 'Select term(s)'

		const allTerms = function() {
			const firstYear = 2018
			const firstYearSemester = 2

			const currentTime = new Date()
			const thisYear = currentTime.getFullYear();

			const semesterMap = {
				1: 'Winter',
				2: 'Spring',
				3: 'Summer',
				4: 'Fall',
			}

			let endSemester
			let endYear
			switch ( currentTime.getMonth() ) {
				case 0 :
				case 1 :
				case 2 :
				case 3 :
				case 4 :
					endSemester = 4
					endYear = thisYear
				break

				default :
					endSemester = 2
					endYear = thisYear + 1
				break
			}

			let terms = []
			let yearStartSemester = 1
			let yearEndSemester = 4
			for ( let theYear = firstYear; theYear <= endYear; theYear++ ) {
				yearStartSemester = ( theYear === firstYear ) ? firstYearSemester : 1
				yearEndSemester = ( theYear === endYear ) ? endSemester : 4

				for ( let theSemester = yearStartSemester; theSemester <= yearEndSemester; theSemester++ ) {
					terms.push( {
						label: getTermName( theYear, theSemester ),
						value: theYear + '-' + theSemester,
					} )
				}
			}

			return terms.reverse()
		}

		const options = allTerms()

		const returnedTerms = ( ! props.attributes.hasOwnProperty( 'terms' ) || '' === props.attributes.terms ) ? [] : JSON.parse( props.attributes.terms );
		const defaultValue = returnedTerms.map( (term) => {
			for ( var theTerm of options ) {
				if ( theTerm.value === term ) {
					return theTerm
				}
			}
		} )

		return (
			<div className="cac-course-term-block">
				<h2>{title}</h2>
				<p>{gloss}</p>

				<Select
					defaultValue={defaultValue}
					isMulti
					onChange={handleChange}
					options={options}
					placeholder={placeholder}
				/>
			</div>
		)
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return (
			<div>&nbsp;</div>
		);
	},
} );
