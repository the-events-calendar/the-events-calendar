( function ( wp ) {

    wp.data.dispatch( 'core/notices' )
        .createNotice(
            'info', 
            '<b>' +  data.title + '</b><p>' + data.description + '</p>', 
            { 
                __unstableHTML: true, 
                isDismissible: true, 
                actions: [ 
                    { 
                        url: data.upgrade_link, 
                        label: 'Start storage migration' 
                    }, 
                    { 
                        url: data.learn_link, 
                        label: 'Learn more' 
                    }
                ] 
            } 
        );
} )( window.wp );