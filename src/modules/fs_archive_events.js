const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
function bob_ross23() {
	return 'bob_ross23';
}
console.log(bob_ross23());
registerBlockType( 'tec/archive-events', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Archive Events' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'Archive Events' ),
		__( 'The Events Calendar' ),
	],
    edit: ( props ) => {
            // Creates a <p class='wp-block-cgb-block-my-first-block'></p>.
            return (
                <div className={ props.className }>
                    <h3>Events Archive</h3>
                    <p>
                        This block serves as a placeholder for your The Events Calendar archive block. It will display the event search fields, and event results.
                    </p>
                    <p>
                        <img src={'https://place-hold.it/600x100/333/fff/000?text=@todo&fontsize=22'} />
                    </p>
                    <p>
                    <img src={'https://place-hold.it/600x100/333/fff/000?text=@todo&fontsize=22'} />
                    </p>
                    <p>
                    <img src={'https://place-hold.it/600x100/333/fff/000?text=@todo&fontsize=22'} />
                    </p>
                </div>
            );
        },

} );
