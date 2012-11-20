jQuery( document ).ready( function ( $ ) {
		var $container = $('#tribe-events-photo-events');
		 $(window).load(function(){ 
		  $container.isotope({
		  	containerStyle: { position: 'relative', overflow: 'visible' }
		  });
		 }); 

 
		// update columnWidth on window resize
		$(window).smartresize(function(){
		  $container.isotope({
		  	containerStyle: { position: 'relative', overflow: 'visible' }
		  });
		}).smartresize();	
});
