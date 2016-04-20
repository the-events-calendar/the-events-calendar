<?php
/**
 * Month Single Event
 * This file contains one event in the month view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

global $post;

/**
 * We build and gather information specific to the individual event prior to
 * the tribe_events_template_data() call to reduce the opportunities for 3rd
 * party code to call wp_reset_postdata() or similar, which can result in the
 * $post global referencing something other than the event we're interested
 * in.
 */
$day      = tribe_events_get_current_month_day();
$event_id = "{$post->ID}-{$day['daynum']}";
$link     = tribe_get_event_link( $post );
$title    = get_the_title( $post );

/**
 * How to Use the Javascript Templating System in this View
 * ========================================================
 *
 * Month view (and week in events pro) has implemented javascript templating to power its rich tooltips and mobile views
 * as of Events Version 3.3. This is a guide on how to modify, extend and use this functionality.
 *
 * 1) Overview
 *
 * As of version 3.3 our tooltips and mobile views use a custom javascript templating solution.
 *
 * How it works: event data for each event - such as title, start and end time, excerpt etc - is stored on a data
 * attribute tagged "data-tribejson" in the markup. This particular json works with simple single level key value pairs.
 * The key is used in the javascript template to call our value output.
 *
 * The javascript templates are stored in two new files in the views folder, mobile.php and tooltip.php. You can modify
 * these templates as you wish, and add new data to it for use either in these templates or anywhere on these views. The
 * javascript templates themselves are explained further on.
 *
 * This "data-tribejson" attribute contains a string of valid json that must have its double quotes escaped correctly so
 * it can be used both on a data att and for use in jquery's json methods. Scary? Dont worry, we've taken care of this
 * encoding for you, find out how in the next section.
 *
 * 2) The Template Tags
 *
 * Two new template tags have been introduced to power this system:
 *
 *     tribe_events_template_data()
 *     tribe_prepare_for_json().
 *
 * tribe_events_template_data( $post_object, $additional_data )
 * ============================================================
 *
 * This is the main template tag that will output the string of valid json in the template file. It takes the event post
 * object and an optional php array with additional data for output in the json string. Right now we use this only in
 * month view in events, and week view in events pro. You can add it to other view files if you want handy event data
 * for use in your own javascript. The stock template tag supplies this json string (remember, the key on the left is
 * what we use in the javascript template file to call the data on the right):
 *
 *	{
 *		"eventId": POST ID,
 *		"title": "POST TITLE",
 *		"permalink": "POST PERMALINK",
 *		"startTime": "EVENT START TIME",
 *		"endTime": "EVENT END TIME (MAY NOT BE SET)",
 *		"imageSrc": "IMAGE THUMB FOR MOBILE(MAY NOT BE SET)",
 *		"imageTooltipSrc": "IMAGE THUMB FOR TOOLTIP(MAY NOT BE SET)",
 *		"excerpt": "POST EXCERPT"
 *	}
 *
 * tribe_prepare_for_json( $string )
 * =================================
 *
 * This template tag is used internally by the previous tag, but has been made public for your use as well. Please do
 * note that any additional params you pass into tribe_events_template_data() will automatically be passed through this
 * function and so you need not do that step manually.
 *
 * Lets say we want to add our own dynamic data from custom post meta to the javascript template for mobile. For now
 * lets say that the key name we want to use is "hello" in our js template. The following example shows how we would go
 * about adding the custom post meta and appending it to our event json string that is output in the markup.
 *
 *    $additional_data = array();
 *    $string = get_post_meta( get_the_ID(), 'hello_meta' ); // this string can be anything
 *    $additional_data['hello'] = $string;
 *    echo tribe_events_template_data( $post, $additional_data ); ?>
 *
 * Explanation: we create an empty array to cram our data into. We can add as much as we want, there are no limits on
 * data attribute length in the html5 spec. We want to call this data with the word "hello" in the js template, so that
 * is the key name we give it in the php array.
 *
 * After we have our data we supply it along with the post object. Now we'll cover the javascript template.
 *
 * 3) The Javascript Templates
 *
 * As said earlier the templates are stored in tooltip.php and mobile.php in the month view folder. Javascript templates
 * are simply standard html markup with keys wrapped in an expression that our parsing function uses to populate the
 * values to. Our expression is a [[, THE KEY, followed by a ]]
 *
 * The template has 3 modes when parsing these.
 *
 *    [[ ]] is for executing javascript you place between the expression. Note below how we test for the presence of an
 *          end time and image thumbnail using this.
 *
 *    [[= ]] Is for a string with escaped html.
 *
 *    [[=raw ]] Is for a string with html preserved. make sure to test for xss vulnerabilities using this method.
 *
 * Now lets look at the tooltip template. Compare the keys in it to the json string in section 2 above to map out whats
 * going on.
 *
 *
 *
 *	<script type="text/html" id="tribe_tmpl_tooltip">
 *		<div id="tribe-events-tooltip-[[=eventId]]" class="tribe-events-tooltip">
 *			<h4 class="tribe-event-title">[[=title]]</h4>
 *			<div class="tribe-events-event-body">
 *				<div class="tribe-event-duration">
 *					<abbr class="tribe-events-abbr tribe-event-date-start">[[=startTime]] </abbr>
 *			[[ if(endTime.length) { ]]
 *					-<abbr class="tribe-events-abbr tribe-event-date-end"> [[=endTime]]</abbr>
 *			[[ } ]]
 *				</div>
 *			[[ if(imageTooltipSrc.length) { ]]
 *				<div class="tribe-events-event-thumb">
 *					<img src="[[=imageTooltipSrc]]" alt="[[=title]]" />
 *				</div>
 *			[[ } ]]
 *			[[ if(excerpt.length) { ]]
 *				<p class="entry-summary description">[[=raw excerpt]]</p>
 *			[[ } ]]
 *				<span class="tribe-events-arrow"></span>
 *			</div>
 *		</div>
 *	</script>
 *
 *
 * Please note when creating your own data to feed to this that you must supply the key every time, even if the value is
 * empty. The templating function will error if one of the keys in the template is missing from the json.
 *
 * 4) Using the JSON String In Your Own Javascript
 *
 * OK, so we've gone though how to use, modify and extend the existing templates. But you've probably noticed "Hey, I
 * have all the event data for each event in a tidy json string, I want to use that in my own js for something"
 *
 * Its really quite easy.
 *
 * Plain javascript to loop over all events in grid view, get the json string, convert to object and log event title:
 *
 * 	(function (window, document) {
 *
 *		var events = document.querySelectorAll('.tribe_events');
 *
 *		for (var i=0; i < events.length; i++) {
 *
 *			var event = events[i],
 *				data = event.getAttribute('data-tribejson'),
 *				obj = JSON.parse(data);
 *
 *			console.log('Event title is: ' + obj.title);
 *		}
 *
 *	})(window, document);
 *
 * Same thing in jQuery:
 *
 *	(function (window, document, $) {
 *
 *		$(document).ready(function () {
 *
 *			$('.tribe_events')
 *				.each(function () {
 *
 *					var obj = $(this).data('tribejson');
 *
 *					console.log('Event title is: ' + obj.title);
 *
 *				});
 *
 *		});
 *
 *	})(window, document, jQuery);
 *
 */
?>

<div id="tribe-events-event-<?php echo esc_attr( $event_id ); ?>" class="<?php tribe_events_event_classes() ?>" data-tribejson='<?php echo esc_attr( tribe_events_template_data( $post ) ); ?>'>
	<h3 class="tribe-events-month-event-title"><a href="<?php echo esc_url( $link ) ?>" class="url"><?php echo $title ?></a></h3>
</div><!-- #tribe-events-event-# -->

