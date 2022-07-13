( function ( wp ) {

    const { __ } = wp.i18n;

    wp.data.dispatch( 'core/notices' )
        .createNotice(
            'warning', 
            '<b>' +  data.title + '</b><p>' + data.description + '</p>', 
            { 
                __unstableHTML: true, 
                isDismissible: true, 
                actions: [ 
                    { 
                        url: data.upgrade_link, 
                        label: __( 'Start storage migration', 'the-events-calendar' )
                    }, 
                    { 
                        url: data.learn_link, 
                        label: __( 'Learn more', 'the-events-calendar' )
                    }
                ] 
            } 
        );
} )( window.wp );