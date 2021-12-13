/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.bootstrap_barrio = {
    attach: function (context, settings) {

      // global navbar disable doptdown-toggle on click. Instead, just follow link.
      $(".region-nav-main a.dropdown-toggle").removeAttr( "data-toggle" );
    }
  };

})(jQuery, Drupal);
