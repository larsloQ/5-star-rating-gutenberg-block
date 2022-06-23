// index.js
const { registerBlockType } = wp.blocks;

const { useSelect } = wp.data;
const { useBlockProps } = wp.blockEditor || wp.editor;

import attributes from './attributes';
import Edit from './edit';

 
registerBlockType( 'yours59/yours59-stars-rating', {
    apiVersion: 2,
    title: 'A manual 5-star-rating block',
    description: 'Use Color->Text to set color of stars',
    icon: 'star-half',
    category: 'yours59',
    attributes,
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        return (
            <div>
                <Edit 
                    attributes={attributes}
                    setAttributes={setAttributes}    
                />
            </div>
        )
    },
    supports: { 
        color: true,
        spacing: {
            margin: true,
            padding: true
        }
    },
    
} );
