/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.2
 *
 * @type   {Object}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  4.9.2
 *
 * @type   {Object}
 */
tribe.events.views.manager = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.2
 *
 * @param {Object} $   jQuery
 * @param {Object} _   Underscore.js
 * @param {Object} obj tribe.events.views.manager
 *
 * @return {void}
 */
(function($, _, obj) {
  "use strict";
  var $window = $(window);

  /**
   * If found, will store the nonce from view responses.
   *
   * @since 6.1.4
   *
   * @type {String|null}
   */
  obj.nonces = null;

  /**
   * Selectors used for configuration and setup
   *
   * @since 4.9.2
   *
   * @type {Object}
   */
  obj.selectors = {
    container: '[data-js="tribe-events-view"]',
    form: '[data-js="tribe-events-view-form"]',
    link: '[data-js="tribe-events-view-link"]',
    dataScript: '[data-js="tribe-events-view-data"]',
    loader: ".tribe-events-view-loader",
    loaderText: ".tribe-events-view-loader__text",
    hiddenElement: ".tribe-common-a11y-hidden",
    nonceScript: '[data-js="tribe-events-view-nonce-data"]'
  };

  /**
   * Object with the details of the last location URL.
   *
   * @since 5.7.0
   *
   * @type {{origin: string, pathname: string}}
   */
  obj.lastLocation = {
    origin: "",
    pathname: ""
  };

  /**
   * Flag when a popstate change is happening.
   *
   * @since 4.9.12
   *
   * @type {boolean}
   */
  obj.doingPopstate = false;

  /**
   * Stores the current ajax request been handled by the manager.
   *
   * @since 4.9.12
   *
   * @type {jqXHR|null}
   */
  obj.currentAjaxRequest = null;

  /**
   * Stores the last container that used PushState, which prevents fails.
   *
   * @todo @bordoni @paul once shortcodes start managing URLs this will need
   *       to improve to a full tracker of history.
   *
   * @since 4.9.12
   *
   * @type {jQuery}
   */
  obj.$lastContainer = $();

  /**
   * Containers on the current page that were initialized.
   *
   * @since 4.9.2
   *
   * @type {jQuery}
   */
  obj.$containers = $();

  /**
   * Clean up the container and event listeners
   *
   * @since 5.0.0
   *
   * @param  {jQuery} container Which element we are going to clean up
   *
   * @return {void}
   */
  obj.cleanup = function(container) {
    var $container = $(container);
    var $form = $container.find(obj.selectors.form);
    var $data = $container.find(obj.selectors.dataScript);
    var data = {};

    // If we have data element set it up.
    if ($data.length) {
      data = JSON.parse($data.text().trim());
    }

    $container.trigger("beforeCleanup.tribeEvents", [$container, data]);

    $container
      .find(obj.selectors.link)
      .off("click.tribeEvents", obj.onLinkClick);

    if ($form.length) {
      $form.off("submit.tribeEvents", obj.onSubmit);
    }

    // Remove resize listener for focus order management.
    $container.off("resize.tribeEvents");

    $container.trigger("afterCleanup.tribeEvents", [$container, data]);
  };

  /**
   * Setup the container for views management
   *
   * @since 4.9.2
   *
   * @todo  Requirement to setup other JS modules after hijacking Click and Submit
   *
   * @param  {Integer}        index     jQuery.each index param
   * @param  {Element|jQuery} container Which element we are going to setup
   *
   * @return {void}
   */
  obj.setup = function(index, container) {
    // Get any potential nonces from page load.
    var $nonces = $($.find(obj.selectors.nonceScript));
    var $container = $(container);
    var $form = $container.find(obj.selectors.form);
    var $data = $container.find(obj.selectors.dataScript);
    var data = {};

    // If we have nonces let's update our local nonce vars for future requests.
    if ($nonces.length) {
      obj.nonces = JSON.parse(
        $($nonces[0])
          .text()
          .trim()
      );
      $nonces.remove();
    }

    // If we have data element set it up.
    if ($data.length) {
      data = JSON.parse($data.text().trim());
    }

    $container.trigger("beforeSetup.tribeEvents", [index, $container, data]);
    $container
      .find(obj.selectors.link)
      .on("click.tribeEvents", obj.onLinkClick);

    // Only catch the submit if properly setup on a form
    if ($form.length) {
      $form.on("submit.tribeEvents", obj.onSubmit);
    }

    $container.trigger("afterSetup.tribeEvents", [index, $container, data]);
  };

  /**
   * Given an Element determines it's view container
   *
   * @since 4.9.2
   *
   * @param  {Element|jQuery} element Which element we getting the container from
   *
   * @return {jQuery}
   */
  obj.getContainer = function(element) {
    var $element = $(element);

    if (!$element.is(obj.selectors.container)) {
      return $element.parents(obj.selectors.container).eq(0);
    }

    return $element;
  };

  /**
   * Given an Element determines it's view container data from the script.
   *
   * @since 4.9.2
   *
   * @param  {jQuery} $container Which element we getting the data from.
   *
   * @return {mixed}
   */
  obj.getContainerData = function($container) {
    var $data = $container.find(obj.selectors.dataScript);

    // Bail in case we dont find data script.
    if (!$data.length) {
      return;
    }

    var data = JSON.parse($data.text().trim());

    return data;
  };

  /**
   * Given an container determines if it should manage URL.
   *
   * @since 4.9.4
   *
   * @param  {Element|jQuery} $container Which element we are using as the container.
   *
   * @return {Boolean}
   */
  obj.shouldManageUrl = function($container) {
    var shouldManageUrl = $container.data("view-manage-url");
    var tribeIsTruthy = /^(true|1|on|yes)$/;

    // When undefined we use true as the default.
    if (typeof shouldManageUrl === typeof undefined) {
      shouldManageUrl = true;
    } else {
      // When not undefined we cast as string and test for valid boolean truth.
      shouldManageUrl = tribeIsTruthy.test(String(shouldManageUrl));
    }

    return shouldManageUrl;
  };

  /**
   * Using data passed by the Backend once we fetch a new HTML via an
   * container action.
   *
   * Usage, on the AJAX request we will pass data back using a <script>
   * formatted as a `application/json` that we will parse and apply here.
   *
   * @since 4.9.4
   * @since TBD Added compatibility for when the day view is disabled.
   *
   * @param  {jQuery} $container Which element we are updating the URL from.
   *
   * @return {void}
   */
  obj.updateUrl = function($container) {
    // When handling popstate (browser back/next) it will not handle this part.
    if (obj.doingPopstate) {
      return;
    }

    // Bail when we dont manage URLs
    if (!obj.shouldManageUrl($container)) {
      return;
    }

    var $data = $container.find(obj.selectors.dataScript);

    // Bail in case we dont find data script.
    if (!$data.length) {
      return;
    }

    var data = JSON.parse($data.text().trim());

    // Bail when the data is not a valid object
    if (!_.isObject(data)) {
      return;
    }

    // Bail when URL is not present
    if (_.isUndefined(data.url)) {
      return;
    }

    // Bail when Title is not present
    if (_.isUndefined(data.title)) {
      return;
    }

    /**
     * Compatibility for browsers updating title
     */
    document.title = data.title;

    // Rewrite it to query-param form and falls back to the default view
    // in case day-view is disabled, to prevent 404 after a page refresh.
    var pushUrl = data.url;
    if ( data.day_view_disabled ) {
      try {
        var parsedUrl = new URL( pushUrl, window.location.origin );
        var dateMatch = parsedUrl.pathname.match( /\/(\d{4}-\d{2}-\d{2})\/?$/ );
        if ( dateMatch ) {
          parsedUrl.pathname = parsedUrl.pathname.replace( /\/\d{4}-\d{2}-\d{2}\/?$/, '/' );
          parsedUrl.searchParams.set( 'eventDate', dateMatch[ 1 ] );
          pushUrl = parsedUrl.toString();
        }
      } catch ( e ) {}
    }

    // Push browser history
    window.history.pushState(null, data.title, pushUrl);
    obj.lastLocation.pathname = document.location.pathname;
    obj.lastLocation.origin = document.location.origin;
  };

  /**
   * Hijacks the link click and passes the URL as param for REST API
   *
   * @since 4.9.2
   * @since 6.15.12 Allow the url to be passed as an href or data-url attribute on the link.
   *
   * @param  {Event} event DOM Event related to the Click action
   *
   * @return {boolean}
   */
  obj.onLinkClick = async function(event) {
    var $container = obj.getContainer(this);

    $container.trigger("beforeOnLinkClick.tribeEvents", event);

    event.preventDefault();

    var containerData = obj.getContainerData($container);

    var $link = $(this);
    var url = $link.data("url") || $link.attr("href");
    var prevUrl = containerData.prev_url;
    var shouldManageUrl = obj.shouldManageUrl($container);
    var shortcodeId = $container.data("view-shortcode");

    var data = {
      prev_url: encodeURI(decodeURI(prevUrl)),
      url: encodeURI(decodeURI(url)),
      should_manage_url: shouldManageUrl
    };

    if (shortcodeId) {
      data.shortcode = shortcodeId;
    }

    await obj.request(data, $container);

    $container.trigger("afterOnLinkClick.tribeEvents", event);

    return false;
  };

  /**
   * Hijacks the form submit passes all form details to the REST API
   *
   * @since 4.9.2
   *
   * @todo  make sure we are only capturing fields on our Namespace
   *
   * @param  {Event} event DOM Event related to the Click action
   *
   * @return {boolean}
   */
  obj.onSubmit = async function(event) {
    var $container = obj.getContainer(this);
    $container.trigger("beforeOnSubmit.tribeEvents", event);

    event.preventDefault();

    // The submit event is triggered on the form, not the container.
    var $form = $(this);

    var formData = Qs.parse($form.serialize());

    var data = {
      view_data: formData["tribe-events-views"]
    };

    // Pass the data to the request reading it from `tribe-events-views`.
    await obj.request(data, $container);

    $container.trigger("afterOnSubmit.tribeEvents", event);

    return false;
  };

  /**
   * Catches the normal browser interactions for Next and Previous pages
   * so that we can use the manager to load the page requested instead
   * of just changing the URL.
   *
   * @since  4.9.12
   *
   * @param  {Event} event DOM Event related to the window popstate
   *
   * @return {boolean}     Will always return false on this one.
   */
  obj.onPopState = async function(event) {
    var target = event.originalEvent.target;
    var url = target.location.href;
    var $container = obj.getLastContainer();

    // We are at the same URL + path as before so not really a change on the
    // actual URL happen, it might be just a hash change which shouldn't
    // trigger and XHR request.
    // eslint-disable-next-line max-len
    if (
      obj.lastLocation.origin === target.location.origin &&
      obj.lastLocation.pathname === target.location.pathname
    ) {
      return false;
    }

    obj.lastLocation.pathname = document.location.pathname;
    obj.lastLocation.origin = document.location.origin;

    if (!$container) {
      return false;
    }

    if (obj.currentAjaxRequest) {
      obj.currentAjaxRequest.abort();
    }

    // Flag that we are doing popstate globally.
    obj.doingPopstate = true;

    $container.trigger("beforePopState.tribeEvents", event);

    var data = {
      url: url
    };

    await obj.request(data, $container);

    return false;
  };

  /**
   * Sets up the request data for AJAX request.
   *
   * @since 5.2.0
   *
   * @param  {object}         data       Data object to modify and setup.
   * @param  {Element|jQuery} $container Which container we are dealing with.
   *
   * @return {void}
   */
  obj.setupRequestData = function(data, $container) {
    var shouldManageUrl = obj.shouldManageUrl($container);
    var containerData = obj.getContainerData($container);

    if (!data.url) {
      data.url = containerData.url;
    }

    if (!data.prev_url) {
      data.prev_url = containerData.prev_url;
    }

    data.should_manage_url = shouldManageUrl;

    // Add our nonces if available.
    if (obj.nonces) {
      data = $.extend(data, obj.nonces);
    }

    // Allow other values to be passed to request from container data.
    var requestData = $container.data("tribeRequestData");

    if (!$.isPlainObject(requestData)) {
      return data;
    }

    return $.extend(requestData, data);
  };

  /**
   * Performs an AJAX request given the data for the REST API and which container
   * we are going to pass the answer to.
   *
   * @since 4.9.2
   *
   * @param  {object}         data              DOM Event related to the Click action
   * @param  {Element|jQuery} $container        Which container we are dealing with
   * @param  {object}         overwriteSettings Options for the request
   *
   * @return {void}
   */
  obj.request = async function(data, $container, overwriteSettings = {}) {
    wp.hooks.doAction("tec.events.beforeRequest", data, $container);
    /**
     * Trigger the beforeRequest event.
     *
     * @since 4.9.2
     *
     * @param {Object} data The data passed to the request.
     * @param {Element|jQuery} $container The container we are dealing with.
     */
    $container.trigger("beforeRequest.tribeEvents", [data, $container]);

    var settings = obj.getAjaxSettings($container);

    // Pass the data setup to the $.ajax settings
    settings.data = obj.setupRequestData(data, $container);

    // Shrink the URL components, to try to avoid the URL length limit.
    settings.data = obj.shirinkUrlComponents($container, settings.data);

    // Try to avoid the URL length limit for GET requests.
    try {
      const fullURL = new URL(settings.url, window.location.origin);
      fullURL.search = new URLSearchParams(settings.data).toString();

      if (fullURL.toString().length > 2048) {
        settings.method = "POST";
      }
    } catch (error) {}

    // Make the request.
    obj.currentAjaxRequest = await $.ajax(
      $.extend(settings, overwriteSettings)
    );

		// Since we await the ajax request, we need to call the complete event manually.
		obj.ajaxComplete( $container );

    /**
     * Trigger the afterRequest event.
     *
     * @since 4.9.2
     *
     * @param {Object} data The data passed to the request.
     * @param {Element|jQuery} $container The container we are dealing with.
     */
    $container.trigger("afterRequest.tribeEvents", [data, $container]);

    wp.hooks.doAction("tec.events.afterRequest", data, $container);
  };

  /**
   * Shrink the URL components to the minimum required to identify the view.
   *
   * @since 6.11.1
   *
   * @param {Element|jQuery} $container  Which container we are dealing with
   * @param {object}          components The components to shrink.
   *
   * @return {object} The shrunken components.
   */
  obj.shirinkUrlComponents = ($container, components) => {
    const {
      home_url,
      rest_url,
      rest_nonce,
      rest_method
    } = obj.getContainerData($container);

    return _.reduce(
      components,
      (acc, value, key) => {
        if (key === "prev_url") {
          acc["pu"] = value.replace(home_url, "");
        } else if (key === "url") {
          acc["u"] = value.replace(home_url, "");
        } else if (key === "should_manage_url") {
          acc["smu"] = value;
        } else {
          acc[key] = value;
        }
        return acc;
      },
      {}
    );
  };

  /**
   * Gets the jQuery.ajax() settings provided a views container
   *
   * @since 4.9.2
   *
   * @param  {Element|jQuery} $container Which container we are dealing with
   *
   * @return {Object}
   */
  obj.getAjaxSettings = function($container) {
    var ajaxSettings = {
      url: $container.data("view-rest-url"),
      method: $container.data("view-rest-method") || "POST",
      async: true, // async is keyword
      beforeSend: obj.ajaxBeforeSend,
      success: obj.ajaxSuccess,
      error: obj.ajaxError,
      context: $container
    };

    return ajaxSettings;
  };

  /**
   * Triggered on jQuery.ajax() beforeSend action, which we hook into to
   * setup a Loading Lock, as well as trigger a before and after hook, so
   * third-party developers can always extend all requests
   *
   * Context with the View container used to fire this AJAX call
   *
   * @since 4.9.2
   *
   * @param  {jqXHR}       jqXHR    Request object
   * @param  {Object} settings Settings that this request will be made with
   *
   * @return {void}
   */
  obj.ajaxBeforeSend = function(jqXHR, settings) {
    var $container = this;
    var $loader = $container.find(obj.selectors.loader);

    $container.trigger("beforeAjaxBeforeSend.tribeEvents", [jqXHR, settings]);

    if ($loader.length) {
      $loader.removeClass(obj.selectors.hiddenElement.className());
      var $loaderText = $loader.find(obj.selectors.loaderText);
      $loaderText.text($loaderText.text());
    }
    $container.attr("aria-busy", "true");

    $container.trigger("afterAjaxBeforeSend.tribeEvents", [jqXHR, settings]);
  };

  /**
   * Triggered on jQuery.ajax() complete action, which we hook into to
   * removal of Loading Lock, as well as trigger a before and after hook,
   * so third-party developers can always extend all requests
   *
   * Context with the View container used to fire this AJAX call
   *
   * @since 4.9.2
   * @since 6.11.1 Remove not needed parameters. Hook into the success event instead for those.
   *
   * @param {Element|jQuery} $container The container we are dealing with
   *
   * @return {void}
   */
  obj.ajaxComplete = function( $container ) {
    var $loader = $container.find(obj.selectors.loader);

    $container.trigger("beforeAjaxComplete.tribeEvents", []);

    if ($loader.length) {
      $loader.addClass(obj.selectors.hiddenElement.className());
    }

    $container.trigger("afterAjaxComplete.tribeEvents", []);

    // Flag that we are done with popstate if that was the case.
    if (obj.doingPopstate) {
      obj.doingPopstate = false;
    }

    // Reset the current ajax request on the manager object.
    obj.currentAjaxRequest = null;
  };

  /**
   * Triggered on jQuery.ajax() success action, which we hook into to
   * replace the contents of the container which is the base behavior
   * for the views manager, as well as trigger a before and after hook,
   * so third-party developers can always extend all requests
   *
   * Context with the View container used to fire this AJAX call
   *
   * @since 4.9.2
   *
   * @param  {String} html       HTML sent from the REST API
   * @param  {String} textStatus Status for the request
   * @param  {jqXHR}  qXHR       Request object
   *
   * @return {void}
   */
  obj.ajaxSuccess = function(data, textStatus, jqXHR) {
    var $container = this;
    $container.trigger("beforeAjaxSuccess.tribeEvents", [
      data,
      textStatus,
      jqXHR
    ]);

    var $html = $(data.html);

    // Let's pull out our nonce data.
    var $nonces = $html.find(obj.selectors.nonceScript);
    $html = $html.not(obj.selectors.nonceScript);
    if ($nonces.length) {
      obj.nonces = JSON.parse(
        $($nonces[0])
          .text()
          .trim()
      );
    }

    // Clean up the container and event listeners
    obj.cleanup($container);

    /*
     * Dispatch an event before the container is replaced; bound events are
     * removed!
     */
    document.dispatchEvent(
      new CustomEvent("containerReplaceBefore.tribeEvents", {
        detail: $container
      })
    );

    // Replace the current container with the new Data.
    $container.replaceWith($html);
    $container = $html;

    // Setup the container with the data received.
    obj.setup(0, $container);

    // Dispatch an event after the container is replaced and set up.
    document.dispatchEvent(
      new CustomEvent("containerReplaceAfter.tribeEvents", {
        detail: $container
      })
    );

    // Update the global set of containers with all of the manager object.
    obj.selectContainers();

    // Trigger the browser pushState
    obj.updateUrl($container);

    $container.trigger("afterAjaxSuccess.tribeEvents", [
      data,
      textStatus,
      jqXHR
    ]);

    if (obj.shouldManageUrl($container)) {
      obj.$lastContainer = $container;
    }
  };

  /**
   * Triggered on jQuery.ajax() error action, which we hook into to
   * display error and keep the user on the same "page", as well as
   * trigger a before and after hook, so third-party developers can
   * always extend all requests
   *
   * Context with the View container used to fire this AJAX call
   *
   * @since 4.9.2
   *
   * @param  {jqXHR}       jqXHR    Request object
   * @param  {Object} settings Settings that this request was made with
   *
   * @return {void}
   */
  obj.ajaxError = function(jqXHR, settings) {
    var $container = this;

    $container.trigger("beforeAjaxError.tribeEvents", [jqXHR, settings]);

    /**
     * @todo  we need to handle errors here
     */

    $container.trigger("afterAjaxError.tribeEvents", [jqXHR, settings]);
  };

  /**
   * Saves all the containers in the page into the object.
   *
   * @since  4.9.12
   *
   * @return {jQuery} Which containers were selected.
   */
  obj.selectContainers = function() {
    obj.$containers = $(obj.selectors.container);
    return obj.$containers;
  };

  /**
   * Selects the last container to change the URL.
   *
   * @since  4.9.12
   *
   * @return {jQuery} Last container element.
   */
  obj.getLastContainer = function() {
    /**
     * @todo @bordoni @paul improve this when shortcodes are also managing the URL.
     */
    if (!obj.$lastContainer.length) {
      obj.$lastContainer = obj.$containers
        .filter('[data-view-manage-url="1"]')
        .eq(0);
    }

    return obj.$lastContainer;
  };

  /**
   * Manages focus order for mobile devices.
   *
   * On mobile, ensures focus order is:
   * 1. Datepicker button (.tribe-events-c-top-bar__datepicker)
   * 2. Events bar buttons (.tribe-events-header__events-bar)
   * 3. Content (after .tribe-events-header)
   *
   * @since  6.15.16
   *
   * @param  {jQuery} $container Which container we are managing focus for.
   *
   * @return {void}
   */
  obj.manageMobileFocusOrder = function($container) {
    var containerState = $container.data("tribeEventsState");
    var mobileBreakpoint =
      (tribe.events.views.breakpoints &&
        tribe.events.views.breakpoints.breakpoints &&
        tribe.events.views.breakpoints.breakpoints.medium) ||
      768;
    var isMobile =
      containerState && typeof containerState.isMobile !== "undefined"
        ? containerState.isMobile
        : $container.outerWidth() < mobileBreakpoint;

    if (!isMobile) {
      // On desktop: restore natural tab order by removing tabindex values we set.
      var $header = $container.find("header.tribe-events-header");
      if ($header.length) {
        $header.off("focusin.tribeMobileFocusOrder focusout.tribeMobileFocusOrder");
        $header
          .find('[data-tribe-mobile-focus-order]')
          .off("focus.tribeMobileFocusOrder keydown.tribeMobileFocusOrder blur.tribeMobileFocusOrder")
          .each(function() {
            var $el = $(this);
            var marker = $el.attr("data-tribe-mobile-focus-order");
            // Remove tabindex for skipped elements.
            if (marker === "skipped" || marker === "events-bar-skipped") {
              $el.removeAttr("tabindex");
            }
            $el.removeAttr("data-tribe-mobile-focus-order");
          });
      }
      return;
    }

    // Find the header element - we only manage focus order WITHIN this header.
    var $header = $container.find("header.tribe-events-header");
    if (!$header.length) {
      return;
    }

    // Datepicker button - should be first focusable element WITHIN the header.
    var $datepickerButton = $header.find(
      '[data-js="tribe-events-top-bar-datepicker-button"]'
    );

    // Events bar container - buttons should come second WITHIN the header.
    var $eventsBar = $header.find(".tribe-events-header__events-bar");

    // Find all focusable elements WITHIN the header.
    var $headerFocusable = $header.find(
      "a, button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex='-1'])"
    );

    // Get events bar buttons first to exclude them from the skip logic.
    var $eventsBarButtons = $eventsBar.length
      ? $eventsBar.find(
          "button:not([disabled]), a, input:not([disabled]), select:not([disabled])"
        )
      : $();

    // Skip all focusable elements in header except datepicker and events bar.
    $headerFocusable.each(function() {
      var $el = $(this);
      // Don't skip datepicker button or events bar buttons.
      var isDatepicker = $el.is($datepickerButton);
      var isInEventsBar = $el.closest(".tribe-events-header__events-bar").length > 0;
      var isInDatepicker = $el.closest(".tribe-events-c-top-bar__datepicker").length > 0;
      var isEventsBarButton = $eventsBarButtons.length && $eventsBarButtons.index($el) >= 0;

      if (!isDatepicker && !isInEventsBar && !isInDatepicker && !isEventsBarButton) {
        $el.attr("tabindex", "-1");
        $el.attr("data-tribe-mobile-focus-order", "skipped");
      }
    });

    // Since events bar comes before datepicker in DOM, we need to reorder them.
    // We'll skip events bar initially, then enable it when datepicker gets focus.
    // This way: datepicker comes first, then events bar, without interfering with site header.

    // Skip events bar buttons initially (they come before datepicker in DOM).
    if ($eventsBarButtons.length) {
      $eventsBarButtons.each(function() {
        var $button = $(this);
        // Only skip if not already skipped by the previous loop.
        if ($button.attr("data-tribe-mobile-focus-order") !== "skipped") {
          $button.attr("tabindex", "-1");
          $button.attr("data-tribe-mobile-focus-order", "events-bar-skipped");
        }
      });
    }

    // Function to enable events bar buttons.
    var enableEventsBarButtons = function() {
      if ($eventsBarButtons.length) {
        $eventsBarButtons.each(function() {
          var $button = $(this);
          var currentMarker = $button.attr("data-tribe-mobile-focus-order");
          // Enable if it was marked as skipped.
          if (
            currentMarker === "events-bar-skipped" ||
            (currentMarker === "skipped" && $button.closest(".tribe-events-header__events-bar").length)
          ) {
            $button.removeAttr("tabindex");
            $button.attr("data-tribe-mobile-focus-order", "events-bar");
          }
        });
      }
    };

    // Ensure datepicker is focusable (remove any negative tabindex, but don't set positive tabindex).
    if ($datepickerButton.length) {
      var currentTabindex = $datepickerButton.attr("tabindex");
      if (currentTabindex === "-1" || (currentTabindex && parseInt(currentTabindex, 10) < 0)) {
        $datepickerButton.removeAttr("tabindex");
      }
      // Don't set positive tabindex - let it use natural order after site header.
      $datepickerButton.attr("data-tribe-mobile-focus-order", "datepicker");

      // Enable events bar immediately when datepicker gets focus.
      $datepickerButton.off("focus.tribeMobileFocusOrder");
      $datepickerButton.on("focus.tribeMobileFocusOrder", function() {
        enableEventsBarButtons();
      });

      // Intercept Tab key to ensure events bar is enabled and focus goes there.
      $datepickerButton.off("keydown.tribeMobileFocusOrder");
      $datepickerButton.on("keydown.tribeMobileFocusOrder", function(e) {
        // If Tab key is pressed (not Shift+Tab).
        if (e.keyCode === 9 && !e.shiftKey) {
          // Enable events bar buttons first.
          enableEventsBarButtons();

          // If events bar has buttons, redirect focus to first events bar button.
          if ($eventsBarButtons.length) {
            e.preventDefault();
            e.stopPropagation();
            setTimeout(function() {
              $eventsBarButtons.first()[0].focus();
            }, 0);
            return false;
          }
        }
      });

      // After events bar is enabled, skip datepicker to prevent cycling back.
      // We'll do this when focus leaves datepicker.
      $datepickerButton.off("blur.tribeMobileFocusOrder");
      $datepickerButton.on("blur.tribeMobileFocusOrder", function() {
        // After a short delay, if events bar is enabled, skip datepicker.
        setTimeout(function() {
          if ($eventsBarButtons.length && $eventsBarButtons.first().attr("data-tribe-mobile-focus-order") === "events-bar") {
            $datepickerButton.attr("tabindex", "-1");
          }
        }, 100);
      });
    }

    // Function to reset focus order state (restore datepicker, reset events bar).
    var resetFocusOrderState = function() {
      // Restore datepicker (remove tabindex="-1" if we set it).
      if ($datepickerButton.length) {
        var datepickerMarker = $datepickerButton.attr("data-tribe-mobile-focus-order");
        if (datepickerMarker === "datepicker") {
          var currentTabindex = $datepickerButton.attr("tabindex");
          if (currentTabindex === "-1") {
            $datepickerButton.removeAttr("tabindex");
          }
        }
      }
      // Reset events bar buttons back to skipped state.
      if ($eventsBarButtons.length) {
        $eventsBarButtons.each(function() {
          var $button = $(this);
          var buttonMarker = $button.attr("data-tribe-mobile-focus-order");
          if (buttonMarker === "events-bar") {
            $button.attr("tabindex", "-1");
            $button.attr("data-tribe-mobile-focus-order", "events-bar-skipped");
          }
        });
      }
    };

    // Handle focus on events bar buttons - when focus enters events bar, skip datepicker to prevent cycling back.
    if ($eventsBarButtons.length) {
      $eventsBarButtons.off("focus.tribeMobileFocusOrder");
      $eventsBarButtons.on("focus.tribeMobileFocusOrder", function() {
        // When focus enters events bar, skip datepicker to prevent cycling back.
        if ($datepickerButton.length) {
          $datepickerButton.attr("tabindex", "-1");
        }
      });
    }

    // Reset state when focus leaves the header area (goes to content or cycles back).
    $header.off("focusout.tribeMobileFocusOrder");
    $header.on("focusout.tribeMobileFocusOrder", function(e) {
      var $target = $(e.relatedTarget);
      // If focus is moving outside the header (to content or elsewhere), reset state.
      if (!$target || !$target.closest("header.tribe-events-header").length) {
        setTimeout(function() {
          resetFocusOrderState();
        }, 100);
      }
    });

    // Also enable events bar when focus enters the header (fallback).
    $header.off("focusin.tribeMobileFocusOrder");
    $header.on("focusin.tribeMobileFocusOrder", function(e) {
      var $target = $(e.target);
      // If focus is on datepicker or any element in/after datepicker area, enable events bar.
      if (
        $target.is($datepickerButton) ||
        $target.closest(".tribe-events-c-top-bar__datepicker").length ||
        ($datepickerButton.length &&
          $header.find("*").index($target[0]) >= $header.find("*").index($datepickerButton[0]))
      ) {
        enableEventsBarButtons();
      }
    });

    // Navigation menu should be skipped.
    var $nav = $header.find(".tribe-events-c-top-bar__nav");
    var $navLinks = $nav.find("a, button");
    $navLinks.each(function() {
      var $link = $(this);
      $link.attr("tabindex", "-1");
      $link.attr("data-tribe-mobile-focus-order", "skipped");
    });
  };

  /**
   * Initializes mobile focus order management.
   *
   * @since  6.15.16
   *
   * @param {Event}   event      event object for 'afterSetup.tribeEvents' event
   * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
   * @param {jQuery}  $container jQuery object of view container
   *
   * @return {void}
   */
  obj.initMobileFocusOrder = function(event, index, $container) {
    // Use a small delay to ensure viewport state is set by viewport module.
    setTimeout(function() {
      // Manage mobile focus order after viewport state is set.
      obj.manageMobileFocusOrder($container);
    }, 10);

    // Listen to resize events on this container to update focus order.
    $container.on("resize.tribeEvents", function() {
      obj.manageMobileFocusOrder($container);
    });
  };

  /**
   * Handles the initialization of the manager when Document is ready.
   *
   * @since  4.9.2
   *
   * @return {void}
   */
  obj.ready = function() {
    obj.selectContainers().each(obj.setup);
    obj.lastLocation = {
      origin: document.location.origin,
      pathname: document.location.pathname
    };
  };

  // Listen to afterSetup event to manage mobile focus order.
  // Set this up immediately, not in ready(), to ensure it's attached before containers initialize.
  $(document).on(
    "afterSetup.tribeEvents",
    obj.selectors.container,
    obj.initMobileFocusOrder
  );

  // Configure on document ready.
  $(obj.ready);

  // Attaches the popstate method to the window object.
  $window.on("popstate", obj.onPopState);
})(jQuery, window.underscore || window._, tribe.events.views.manager);
