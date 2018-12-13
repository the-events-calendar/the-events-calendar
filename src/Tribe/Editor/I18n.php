<?php
/**
 * Include translations to Gutenberg Ext
 *
 * @since 4.7
 */
class Tribe__Events__Editor__I18n {
	/**
	 * Hook into the required places to make it work
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'admin_enqueue_scripts', array( $this, 'include_inline_script' ) );
	}

	/**
	 * Include the Inline Script with locale
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function include_inline_script( $value ) {
		if ( ! tribe( 'admin.helpers' )->is_post_type_screen( Tribe__Events__Main::POSTTYPE ) ) {
			return false;
		}

		$domain = 'the-events-calendar';
		$translations = get_translations_for_domain( $domain );
		$locale = array(
			'' => (object) array(),
			'prevent-empty' => 'prevent-empty',
		);

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale[ $msgid ] = $entry->translations;
		}

		// Prepare Jed locale data.
		wp_add_inline_script(
			'tribe-events-editor-elements',
			'wp.i18n.setLocaleData( ' . json_encode( $locale ) . ', "' . $domain . '" );',
			'before'
		);
	}
}
