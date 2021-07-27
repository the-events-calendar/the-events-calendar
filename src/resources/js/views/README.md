# The Events Calendar JavaScript

The Events Calendar uses JavaScript to support the functionalities of the views. Below is a breakdown of the main categories of JavaScript we have to support our plugin.

## Utilities

We have two main utility JavaScript files that are by various other scripts: Viewport and Accordion. These can be extended by other The Events Calendar plugins or custom scripts.

### Viewport

The Viewport JavaScript file mainly listens for the window resize event. On window resize, the `isMobile` state is set and a custom event `resize.tribeEvents` is fired on the document element. This custom event is used throughout the application and can be hooked onto to run custom user-defined scripts.

### Accordion

The Accordion JavaScript file offers 2 different ways of using the accordion functionality.

The first method is to add the `data-js="tribe-events-accordion-trigger"` and `aria-controls` attributes to the accordion trigger. The `aria-controls` attribute points to the `id` of the content element. The script binds onto the trigger and listen for clicks. When clicked, the content element will be toggled open or closed.

The second method is to manually initialize the accordion via the `tribe.events.views.accordion.initAccordion` and `tribe.events.views.accordion.initAccordionA11yAttrs` functions. This method still requires the `aria-controls` attribute to point to the `id` of the content element, but does not require the `data-js="tribe-events-accordion-trigger"` attribute. Don't forget to deinit the accordion via the `tribe.events.views.accordion.deinitAccordion` and `tribe.events.views.accordion.deinitAccordionA11yAttrs` functions.

## Breakpoints

The Breakpoints JavaScript is responsible for applying the correct container query classes to the container. See the [PostCSS README.md from The Events Calendar](https://github.com/moderntribe/the-events-calendar/blob/master/src/resources/postcss/README.md) on container queries for more information on the classes. The breakpoint values can also be filtered to customize the breakpoints.

This script is loaded in the header rather than the footer to prevent flash of unstyled content.

## Manager

The Manager JavaScript is responsible for all of the AJAX requests within the application. The datepicker selections, events bar actions, and view and page navigations are all run by this script.

There are a number of custom events that are fired on the container, but there are 2 events that can handle most cases when creating custom JavaScript off of the Manager.

The first is the `afterSetup.tribeEvents` event. This is fired when the container is loaded and the Manager script is ready. The event is also fired after an AJAX request is successful and the new container has been loaded. This is where you can hook into to perform any setup needed for your script.

The second is the `beforeAjaxSuccess.tribeEvents` event. The event fired when an AJAX response has been received but before the existing container is replaced. This is where you can perform any clean-up tasks on the existing container and remove event listeners to prevent memory leaks.

## Extendable

Extendable JavaScript files are scripts that can be extended by other The Events Calendar plugins or custom scripts.

### Multiday Events

The Multiday Events JavaScript allows linking of hidden multiday events (via `opacity: 0`) to the visible bar that represents the multiday event. Hover and focus events on the hidden events will reflect on the visible event bar. This is a fairly specific use case, but can be extended if needed.

Multiday events are currently only supported in Month View. The script will not run on other views. To expand to another view, hook into the `afterMultidayEventsInitAllowedViews.tribeEvents` on the container and modify the `tribeEventsMultidayEventsAllowedViews` data. You should also add the view and selector prefix to the `tribe.events.views.multidayEvents.selectorPrefixes` object. For example, if you want to use the Multiday Events script on list view, you may add:

```
tribe.events.views.multidayEvents.selectorPrefixes.list = '.tribe-events-calendar-list__';
```

The views should contain multiday event elements with the selector prefix and selector suffixes. See the month view multiday events for an example. Classes with `multiday-event-bar-inner--hover` and `multiday-event-bar-inner--focus` as suffixes are added on `hover` and `focus` events, respectively. These can be used to style the multiday event bar.

### Tooltips

The Tooltips JavaScript allows users to add their own custom tooltips using the existing tooltip script.

The tooltips have custom classes applied to them. To add your own class, hook into the `afterTooltipInitTheme.tribeEvents` event on the container and modify the `tribeEventsTooltipTheme` data. This allows you to add styles using your own classes.

To add a tooltip, you will need two components: the target and the tooltip contents. The target should have the attributes `data-js="tribe-events-tooltip"` and `data-tooltip-content` applied to it. The `data-tooltip-content` value should be the `id` of the element that will be the tooltip contents. The tooltip content element should have the `id` attribute assigned to it. It also helps to add the `role="tooltip"` aria attribute for accessibility.

Once that is set up, the tooltips should work. To hide the tooltip contents on the page, add a wrapper element around the tooltip contents and add `display: none;` and `visibility: hidden;` styles.

## Views JavaScript

The remaining JavaScript files power various different parts of the views.

### Datepicker

The Datepicker JavaScript powers the datepicker. This includes the date selection on list and day view and month selection on month view.

### Events Bar Inputs

The Events Bar Inputs JavaScript adds and removes classes to the events bar inputs based on whether the input contains content or not.

### Events Bar

The Events Bar JavaScript powers the mobile and desktop versions of the events bar.

### Month Grid

The Month Grid JavaScript initializes the mobile month grid and allows for keyboard navigation for accessibility.

### Month Mobile Events

The Month Mobile Events JavaScript allows users to view the selected day's events on mobile month view.

### Navigation Scroll

The Navigation Scroll JavaScript scrolls the viewport to the top if the user is 25% down the page after a successful AJAX request.

### View Selector

The View Selector JavaScript powers the view selector in both tab and accordion view.
