<?php
if ( empty( $customFields ) || ! is_array( $customFields ) ) {
	return;
} ?>
<table id="event-meta" class="eventtable">
	<tbody>
	<tr>
		<td colspan="2" class="tribe_sectionheader">
			<h4><?php _e( 'Additional Event Fields', 'tribe-events-calendar-pro' ); ?></h4></td>
	</tr>
	<?php foreach ( $customFields as $customField ): ?>
		<?php $val = get_post_meta( get_the_ID(), $customField['name'], true ) ?>
		<tr>
			<td><?php echo esc_html( stripslashes( $customField['label'] ) ) ?></td>
			<td>
				<?php $options = explode( "\r\n", $customField['values'] ) ?>
				<?php if ( $customField['type'] == 'text' ): ?>
					<input type="text" name="<?php echo esc_attr( $customField['name'] ) ?>" value="<?php echo esc_attr( $val ) ?>" />
				<?php elseif ( $customField['type'] == 'url' ): ?>
					<input type="text" name="<?php echo esc_attr( $customField['name'] ) ?>" value="<?php echo esc_attr( $val ) ?>" />
				<?php
				elseif ( $customField['type'] == 'radio' ): ?>
					<div>
						<label><input type="radio" name="<?php echo esc_attr( $customField['name'] ) ?>" value="" <?php checked( trim( $val ), '' ) ?>/> None</label>
					</div>
					<?php foreach ( $options as $option ): ?>
						<div>
							<label><input type="radio" name="<?php echo esc_attr( $customField['name'] ) ?>" value="<?php echo esc_attr( $option ) ?>" <?php checked( trim( $val ), trim( $option ) ) ?>/> <?php echo esc_html( stripslashes( $option ) ) ?>
							</label></div>
					<?php endforeach ?>
				<?php
				elseif ( $customField['type'] == 'checkbox' ): ?>
					<?php foreach ( $options as $option ): ?>
						<?php $values = explode( "|", $val ); ?>
						<div>
							<label><input type="checkbox" value="<?php echo esc_attr( trim( $option ) ) ?>" <?php checked( in_array( trim( $option ), $values ) ) ?> name="<?php echo esc_attr( $customField['name'] ) ?>[]" /> <?php echo esc_html( stripslashes( $option ) ) ?>
							</label></div>
					<?php endforeach ?>
				<?php
				elseif ( $customField['type'] == 'dropdown' ): ?>
					<select name="<?php echo $customField['name'] ?>">
						<option value="" <?php selected( trim( $val ), "" ) ?>>None</option>
						<?php $options = explode( "\r\n", $customField['values'] ) ?>
						<?php foreach ( $options as $option ): ?>
							<option value="<?php echo esc_attr( $option ) ?>" <?php selected( trim( $val ), trim( $option ) ) ?>><?php echo esc_html( stripslashes( $option ) ) ?></option>
						<?php endforeach ?>
					</select>
				<?php
				elseif ( $customField['type'] == 'textarea' ): ?>
					<textarea name="<?php echo esc_attr( $customField['name'] ) ?>"><?php echo esc_textarea( $val ) ?></textarea>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
