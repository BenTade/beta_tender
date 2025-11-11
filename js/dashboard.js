/**
 * @file
 * JavaScript for the tender dashboard.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.betaTenderDashboard = {
    attach: function (context, settings) {
      // Add any interactive functionality for the dashboard here.
      $('.dashboard-date-group', context).once('betaTenderDashboard').each(function () {
        // Could add AJAX refresh or other dynamic features.
      });
    }
  };

})(jQuery, Drupal);
