const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
function bob_ross23() {
	return 'bob_ross23';
}
console.log(bob_ross23());
registerBlockType( 'tribe/archive-events', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Archive Events' ), // Block title.
	icon: 'shield', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'Archive Events' ),
		__( 'The Events Calendar' ),
	],

    edit: ( props ) => {
            // Creates a <p class='wp-block-cgb-block-my-first-block'></p>.
            return (
                <div className={ props.className }>
                    <p>— archive-events.</p>
                    <p>
                        a archive-events: <code>archive-events</code> is a new Gutenberg block
                    </p>
                    <p>
                        It was created via{ ' ' }
                        <code>
                            <a href="https://github.com/ahmadawais/create-guten-block">
                         todo
                            </a>
                        </code>
                    </p>
                </div>
            );
        },

} );
