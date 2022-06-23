/* our dependencies */
import attributes from './attributes';

/* WordPress dependencies */
import { __ } from 'wp.i18n';
const {jQuery: $} = window;
const {
	Component,
	Fragment,
} = wp.element;
const {
	InspectorControls,
} = wp.blockEditor || wp.editor;
const {
	PanelBody,
	RangeControl,
	ToggleControl,
	TextControl,
} = wp.components;


const Inspector = function( props ) {
		const {attributes, setAttributes} = props;
		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Settings', 'yours59' ) } initialOpen={true}>
						<Fragment>
							<RangeControl
							label={__('Rating ( 0-5 )', 'yours59')}
							value={attributes.rating}
							onChange={ 
								value => { 
									setAttributes({rating:value}); 
								}
							}
							allowReset
							min={0}
							max={5}
							step={.1}
						/>
						
						<RangeControl
							label={__('Size', 'yours59')}
							value={attributes.scale}
							onChange={ 
								value => { 
									setAttributes({scale:value}); 
								}
							}
							allowReset
							min={.1}
							max={1.5}
							step={.1}
						/>
						</Fragment>
				</PanelBody>
			</InspectorControls>
		);

}

export default ( Inspector );