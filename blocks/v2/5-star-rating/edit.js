const { useBlockProps } = wp.blockEditor || wp.editor;
const {
    Component,
    Fragment,
} = wp.element;

const { serverSideRender: ServerSideRender } = wp;
const {  Disabled } = wp.components;

import { __ } from 'wp.i18n';
import Inspector from './inspector';

const Edit = function(props) {
    
        const {
            attributes,
            setAttributes
        } = props;
        console.log(attributes,"attributes")
        const blockProps = useBlockProps();

        return (
              <div { ...blockProps }>
                <Disabled>
                    <ServerSideRender
                        block='yours59/yours59-stars-rating'
                        attributes={attributes}
                        httpMethod='POST'
                    />
                </Disabled>
                 <Inspector
                            attributes={attributes}
                            setAttributes={setAttributes}
                />
            </div>
        )
      
}
export default (Edit);