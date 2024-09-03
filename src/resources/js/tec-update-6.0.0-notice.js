( function ( wp, obj ) {
	obj.data = window.tecBlocksEditorUpdateNoticeData;

    const { __, sprintf } = wp.i18n;

    wp.data.dispatch( 'core/notices' )
        .createNotice(
            'warning',
            `<b>${obj.data.title}</b><p>${obj.data.description}</p>`,
            {
                __unstableHTML: true,
                isDismissible: true,
                actions: [
                    {
                        url: obj.data.upgrade_link,
                        label: sprintf(
							__( 'Upgrade your %1$s', 'the-events-calendar' ),
							obj.data.events_plural_lower
						),
                    },
                    {
                        url: obj.data.learn_link,
                        label: __( 'Learn more', 'the-events-calendar' )
                    }
                ]
            }
        );
} )( window.wp, {} );
