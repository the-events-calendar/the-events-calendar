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
const EventItem = ( { width = '40%' } ) => {
    return (
        <div style={ { width, height: 16, background: '#eee', margin: '18px 4px' } }/>
    )
}


/**
 * The Venue block used in Site Editor templates.
 */
export default {
    id: 'tec/single-venue',
    title: __( 'Single Venue', 'the-events-calendar' ),
    icon: 'list-alt',
    category: 'tribe-events',
    keywords: [
        __( 'Single Venue', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        const { className, ...blockProps } = useBlockProps();

        return (
            <div className={ `${ className } ${ props.className }` } { ...blockProps }>
                <h3>{ __( 'Venue Title', 'the-events-calendar' ) }</h3>
                <div style={ { float: 'left', width: '40%' } }>
                    <EventItem width={ '70%' }/>
                    <EventItem width={ '84%' }/>
                    <EventItem width={ '73%' }/>
                    <EventItem width={ '63%' }/>
                </div>
                <div style={ { float: 'left', width: '30%' } }>
                    <div style={ {
                        width: '70%',
                        maxWidth: 340,
                        height: 180,
                        background: '#eee',
                        margin: '18px 4px'
                    } }/>
                </div>
                <div style={{clear:'both', height:1}}></div>
            </div>
        );
    },
};