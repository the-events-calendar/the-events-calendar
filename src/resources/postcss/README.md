# The Events Calendar PostCSS Styles

## Class naming consistency and BEM

A couple of issues we've had previously with templates for Modern Tribe plugins was inconsistent class naming and the class naming structure. To deal with this, we've adopted the use of [BEM](http://getbem.com/naming/) for class naming, combined with the use of `tribe-events-` as a block prefix.

First is the use of [BEM](http://getbem.com/naming/) for class naming (see link for more details). BEM stands for Block Element Modifier. We've used BEM as a guide to help us name classes and maintain consistency. This helps us structure the CSS around the HTML that we are styling without running into class naming chaos.

Secondly, we've added prefixes to our classes. The first prefix we've used is `tribe-events-`. This is mainly to avoid styles clashing with other theme styles. For example, if we used a class `header`, a theme that the user may apply may also use a class `header` and the theme styles may unintentionally affect the plugin styles. Instead, we use `tribe-events-header`. The second prefix we've used is context-based prefixes. Within The Events Calendar, we mainly use `l-` for layout and `c-` for component. Modern Tribe's Common plugin uses more context-based prefixes. These prefixes help determine the context of these reusable style classes. For example, the class `tribe-events-c-search` can be applied, along with the element classes, to a search component to apply search component styles.

## View/block wrapper class

In order to not override theme styles and elements outside of Modern Tribe plugins, we've added a wrapper class `tribe-events` around all of Modern Tribe plugins blocks and views. For example, the markup for a specific view or block might look like the following:

```
<div class="tribe-events">
	...
	<div class="tribe-events-c-search">
		...
	</div>
	...
</div>
```

Given this markup, the PostCSS will look like the following:

```
.tribe-events {
	...

	.tribe-events-c-search {
		/* search component styles here */
	}

	...
}
```

We need the `tribe-events` wrapper class in order to override Common resets and styles, as they use the `tribe-common` wrapper class. This also allows us to target only the elements we intend to target within the Modern Tribe plugin views while reducing the probability of clashing styles with themes.
