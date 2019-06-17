<?php
/**
 * Linked Post metabox
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$linked_post_container  = tribe_get_linked_post_container( $this->post_type );
$linked_post_name       = tribe_get_linked_post_name_field_index( $this->post_type );
$linked_post_name_field = "{$linked_post_container}[{$linked_post_name}][]";

?>
<script type="text/template" id="tmpl-tribe-create-<?php echo esc_attr( $this->post_type ); ?>">
	<tbody class="new-<?php echo esc_attr( $this->post_type ); ?>">
		<tr class="linked-post <?php echo sanitize_html_class( strtolower( $linked_post_name ) ); ?>">
			<td><?php printf( esc_html__( '%s Name:', 'the-events-calendar' ), $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name'] ); ?></td>
			<td>
				<input type='text' name='<?php echo esc_attr( $linked_post_name_field ); ?>' class='linked-post-name <?php echo esc_attr( $this->post_type ); ?>-name' size='25' value='' />
			</td>
		</tr>
		<?php do_action( 'tribe_events_linked_post_new_form', $this->post_type ); ?>
	</tbody>
</script>

<script>
( function( $ ) {
	$('#event_<?php echo esc_js( $this->post_type ); ?>').on( 'blur', '.linked-post-name', function () {
		var input = $(this);
		var group = input.parents('tbody');

		// Not actually populated with anything? Don't bother validating
		if ( ! input.val().length ) {
			return;
		}

		$.post( TEC.ajaxurl, {
				action: 'tribe_event_validation',
				nonce: '<?php echo esc_js( wp_create_nonce( 'tribe-validation-nonce' ) ); ?>',
				type: '<?php echo esc_js( $this->post_type ); ?>',
				name: input.val()
			},
			function ( result ) {
				if ( 1 == result ) {
					group.find('.tribe-<?php echo esc_js( $this->post_type ); ?>-error').remove();
				} else {
					group.find('.tribe-<?php echo esc_js( $this->post_type ); ?>-error').remove();
					input.after('<div class="tribe-<?php echo esc_attr( $this->post_type ); ?>-error error form-invalid"><?php printf( esc_html__( '%s Name Already Exists', 'the-events-calendar' ), $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name'] ); ?></div>');
				}
			}
		);
	})
} )( jQuery );
</script>
