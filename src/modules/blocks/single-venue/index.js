/**
 * External Dependencies
 */
const { __ } = wp.i18n;
const { useBlockProps } = wp.blockEditor;

/**
 * Small component to simplify some pseudo event blocks.
 *
 * @param {Object} props JSX props to pass down.
 * @returns {JSX.Element}
 * @constructor
 */
const FauxLi_ne = ( { style = {}, ...props } ) => {
    const divStyle = { height: 16, background: '#eee', margin: '18px 4px 18px 0' }

    return ( <div style={ { ...divStyle, ...style } } { ...props } /> )
}

/**
 * The Venue block used in Site Editor templates.
 */
export default {
    id: 'tec/single-venue',
    title: __( 'Single Venue', 'the-events-calendar' ),
    icon: 'calendar-alt',
    category: 'tribe-events',
    keywords: [
        __( 'Single Venue', 'the-events-calendar' ),
        __( 'The Events Calendar', 'the-events-calendar' ),
    ],
    edit: ( props ) => {
        const { className, ...blockProps } = useBlockProps();
console.log('here', props)
        return (
            <div className={ `${ className } ${ props.className }` } { ...blockProps }>
                <h3>{ __( 'Venue Title', 'the-events-calendar' ) }</h3>
                <p>
                    <strong>
                        { __( 'EVENT Location', 'the-events-calendar' ) }
                    </strong>
                </p>

                <button type={ "button" }
                        style={ {
                            border: '1px solid rgb(51, 74, 255)',
                            borderRadius: 4,
                            backgroundColor: '#fff',
                            color: 'rgb(51, 74, 255)',
                            fontSize: 14,
                            fontWeight: 700,
                            padding: '8px 12px',
                            textAlign: 'center',
                            width: 200,
                            height: 40,
                            lineHeight: '22px',
                            fontFamily: '"Helvetica Neue", Helvetica, -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif',
                        } }
                >Add to calendar
                </button>
            </div>
        );
    },
};