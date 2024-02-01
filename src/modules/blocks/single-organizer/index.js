/**
 * External Dependencies
 */
const { __ } = wp.i18n;
const { useBlockProps } = wp.blockEditor;


/**
 * Small component to simplify some pseudo event blocks.
 *
 * @param width
 * @returns {JSX.Element}
 * @constructor
 */
const TextPlaceholder = ( { width = '40%' } ) => {
    return (
        <div style={ { width, height: 16, background: '#eee', margin: '18px 4px' } }/>
    )
}


/**
 * The Organizer block used in Site Editor templates.
 */
export default {
    id: 'tec/single-organizer',
    title: __( 'Single Organizer', 'the-events-calendar' ),
    icon: 'list-alt',
    category: 'tribe-events',
    keywords: [
        __( 'Single Organizer', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        const { className, ...blockProps } = useBlockProps();

        return (
            <div className={ `${ className } ${ props.className }` } { ...blockProps }>
                <h3>{ __( 'Organizer Title', 'the-events-calendar' ) }</h3>
                <div style={ { float: 'left', width: '50%' } }>
                    <TextPlaceholder width={ '96%' }/>
                    <TextPlaceholder width={ '98%' }/>
                    <TextPlaceholder width={ '95%' }/>
                    <TextPlaceholder width={ '63%' }/>
                </div>
                <div style={ { float: 'left', paddingLeft: 20, width: '30%' } }>
                    <TextPlaceholder width={ '70%' }/>
                    <TextPlaceholder width={ '68%' }/>
                    <TextPlaceholder width={ '73%' }/>
                    <TextPlaceholder width={ '63%' }/>
                </div>
                <div style={ { clear: 'both', height: 1 } }></div>
            </div>
        );
    },
};