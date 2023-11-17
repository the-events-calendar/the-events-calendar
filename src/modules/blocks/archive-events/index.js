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
 * The Archive Events block used in Site Editor templates.
 */
export default {
    id: 'tec/archive-events',
    title: __( 'Archive Events', 'the-events-calendar' ),
    icon: 'calendar-alt',
    category: 'tribe-events',
    keywords: [
        __( 'Archive Events', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        const { className, ...blockProps } = useBlockProps();

        return (
            <div className={ `${ className } ${ props.className }` } { ...blockProps }>
                <h3>{ __( 'Archive Events', 'the-events-calendar' ) }</h3>
                <p>
                    { __( 'This block serves as a placeholder for your The Events Calendar archive events template. It will display the event search fields and event results.', 'the-events-calendar' ) }
                </p>
                <div>
                    <input type={ "text" } style={ {
                        height: 22,
                        width: '50%',
                        margin: 4,
                        padding: 4,
                        borderRadius: 4,
                        maxWidth: 400
                    } } disabled/>
                </div>
                <EventItem width={ "40%" }/>
                <EventItem width={ "60%" }/>
                <EventItem width={ "35%" }/>
                <EventItem width={ "40%" }/>
                <EventItem width={ "55%" }/>
            </div>
        );
    },
};