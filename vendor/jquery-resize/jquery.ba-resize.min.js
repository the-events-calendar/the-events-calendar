/*
 * jQuery resize event - v1.1 - 3/14/2010
 * http://benalman.com/projects/jquery-resize-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
!function(t,i,e){var h,n=t([]),r=t.resize=t.extend(t.resize,{}),a="setTimeout",o="resize",d=o+"-special-event",s="delay";r[s]=250,r.throttleWindow=!0,t.event.special[o]={setup:function(){if(!r.throttleWindow&&this[a])return!1;var e=t(this);n=n.add(e),t.data(this,d,{w:e.width(),h:e.height()}),1===n.length&&function e(){h=i[a](function(){n.each(function(){var i=t(this),e=i.width(),h=i.height(),n=t.data(this,d);e===n.w&&h===n.h||i.trigger(o,[n.w=e,n.h=h])}),e()},r[s])}()},teardown:function(){if(!r.throttleWindow&&this[a])return!1;var i=t(this);n=n.not(i),i.removeData(d),n.length||clearTimeout(h)},add:function(i){if(!r.throttleWindow&&this[a])return!1;var h;function n(i,n,r){var a=t(this),o=t.data(this,d);o||(o=t.data(this,d,{})),o.w=n!==e?n:a.width(),o.h=r!==e?r:a.height(),h.apply(this,arguments)}if("function"==typeof i)return h=i,n;h=i.handler,i.handler=n}}}(jQuery,this);