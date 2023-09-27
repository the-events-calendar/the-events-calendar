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
const FauxLine = ( {style ={}, ...props } ) => {
    const divStyle = { height: 16, background: '#eee', margin: '18px 4px 18px 0' }

    return ( <div style={{...divStyle, ...style}  } {...props} />    )
}

/**
 * The Archive Events block used in Site Editor templates.
 */
export default {
    id: 'tec/single-event',
    title: __( 'Single Event', 'the-events-calendar' ),
    icon: 'calendar-alt',
    category: 'tribe-events',
    keywords: [
        __( 'Single Event', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        const { className, ...blockProps } = useBlockProps();

        return (
            <div className={ `${ className } ${ props.className }` } { ...blockProps }>
                <h3>{ __( 'Event Title', 'the-events-calendar' ) }</h3>
                <p>
                    <strong>
                    { __( 'EVENT DATE/TIME', 'the-events-calendar' ) }
                </strong>
                </p>

                <FauxLine style={{marginLeft:34, marginRight:'25%'}} />
                <FauxLine style={{marginRight:'25%'}} />
                <FauxLine style={{marginRight:'25%'}} />
                <button type={"button"} class="wp-block-button__link wp-element-button">Subscribe to Event</button>
            </div>
        );
    },
};