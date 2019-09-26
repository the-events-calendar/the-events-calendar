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

## CSS specificity

Given the above structure of using a wrapper class, we've increased the [CSS specificity](https://developer.mozilla.org/en-US/docs/Web/CSS/Specificity) needed for theme developers to override our styles. For class-based styles, the minimum specificity required is 2 classes. With some modifiers, the minimum specificity required may be 3 classes. For example:

```
.tribe-events {
	...

	.tribe-events-calendar-month__day--current {

		.tribe-events-calendar-month__day-date {
			/* month day date styles */
		}
	}

	...
}
```

In this case, the day date is an element of the month day. However, the `--current` modifier is applied to the top level element day. Given this structure, our minimum specificity becomes 3 classes.

For overriding styles, it is recommended to only use classes to keep overriding specificity consistent. All elements should have classes and should be targetted using those classes.

## Modifiers, pseudo-classes, and media queries

As you get into building upon these styles and creating new styles, the order of modifiers, pseudo-classes, and media queries comes into question. The general rule is to apply them in the following order: media queries, pseudo-classes, modifiers. See the examples below:

```
.tribe-events {
	...

	.tribe-events-c-view-selector__button {
		/* view selector button styles */

		@media (--viewport-medium) {
			/* viewport medium view selector button styles */
		}

		&:before {
			/* :before pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :before pseudo-class styles */
			}
		}
	}

	.tribe-events-c-view-selector__button--active {
		/* active view selector button styles */

		@media (--viewport-medium) {
			/* viewport medium active view selector button styles */
		}

		&:before {
			/* :before pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :before pseudo-class styles */
			}
		}
	}

	...
}
```

In the case of an element, we might get the following scenario:

```
.tribe-common {
	...

	.tribe-events-c-view-selector__button {
		/* view selector button styles */

		@media (--viewport-medium) {
			/* viewport medium view selector button styles */
		}

		&:before {
			/* :before pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :before pseudo-class styles */
			}
		}
	}

	.tribe-events-c-view-selector--tabs {

		.tribe-events-c-view-selector__button {
			/* tabs view selector button styles */

			@media (--viewport-medium) {
				/* viewport medium tabs view selector button styles */
			}

			&:before {
				/* :before pseudo-class styles */

				@media (--viewport-medium) {
					/* viewport medium :before pseudo-class styles */
				}
			}
		}
	}

	...
}
```

## Structure of The Events Calendar styles

The Events Calendar styles are comprised of 2 files: `views-skeleton.pcss` and `views-full.pcss`. The views skeleton styles cover basic layout styles for the views and components. The views full styles, combined with the skeleton styles, make up the full suite of styles for The Events Calendar plugin views.

The Events Calendar styles are broken into 4 main sections: utilities, base, components, and views.

### Utilities

The utilities are a set of common PostCSS variables, icons, and mixins used throughout the plugins. These come from the Tribe Common Styles repository. See [Tribe Common Styles](https://github.com/moderntribe/tribe-common-styles) for more details.

### Base

The base styles are very general styles that can be applied to all views. The two main partials are layouts and views. These cover styles for the general layout and view containers.

### Components

Components are groups of reusable markup and styles. The component style structure is meant to mirror the markup structure.

### Views

The views styles are styles that are specific to each view and the sub-elements of each view. These are generally more layout-focused after applying common styles, though some elements, such as in the month view, require more custom styles.

### Media queries

These styles use a mobile-first approach. Given this, there are only `min-width:` breakpoints, never `max-width:` breakpoints. This also lends to using the `--min-medium` modifier.

## Theme overrides

Modern Tribe plugins support a handful of themes. Some themes provide stylesheets that have high specificity for elements and override the plugin styles. To counter this, we've included theme overrides to ensure our plugin styles display as expected with the supported themes.

The specificity to override the styles are matched to those applied to the theme. This means that if, for example, a theme applied an ID and 2 extra classes to a `button` style, we might see the following theme override:

```
.tribe-events {

	/* -------------------------------------------------------------------------
	 * Datepicker: Theme Overrides
	 * ------------------------------------------------------------------------- */

	#id-1 .class-1 .class-2 & {

		.datepicker {
			/* datepicker theme override styles */
		}
	}
}
```

## How to contribute

You want to [contribute](https://github.com/moderntribe/the-events-calendar/blob/master/CONTRIBUTING.md) to these styles? Great! There are a few things to consider when making changes:

### Additions

Additions are generally safe, as long as the selectors do not conflict with existing selectors.

### Alterations

Alterations should be done carefully, as they will affect all element styles using the selectors being altered.

### Deletions

Deletions should also be done carefully, for the same reasons as **Alterations** above. Removing a style from a selector that is still being used will result in unintended styles.
