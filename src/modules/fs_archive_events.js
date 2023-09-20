const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const EventItem = ( { width = '40%' } ) => {
    return (
        <div style={ { width, height: 16, background: '#eee', margin: '18px 4px' } }/>
    )
}
registerBlockType( 'tec/archive-events', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( 'Archive Events', 'the-events-calendar' ), // Block title.
    icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __( 'Archive Events', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        return (
            <div className={ props.className }>
                <h3>{ __( 'Archive Events', 'the-events-calendar' ) }</h3>
                <p>
                    { __( 'This block serves as a placeholder for your The Events Calendar archive block. It will display the event search fields, and event results.', 'the-events-calendar' ) }
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
} );
