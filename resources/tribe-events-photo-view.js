jQuery( document ).ready( function ( $ ) {
		var $container = $('#tribe-events-photo-events');
		 $(window).load(function(){ 
		  $container.isotope();
		 }); 

 
		// update columnWidth on window resize
		$(window).smartresize(function(){
		  $container.isotope();
		}).smartresize();	
});
