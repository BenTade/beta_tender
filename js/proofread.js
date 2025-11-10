/**
 * @file
 * JavaScript for the proofreading dashboard.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.betaTenderProofread = {
    attach: function (context, settings) {
      // Add any interactive functionality for the proofreading dashboard here.
      $('.status-group', context).once('betaTenderProofread').each(function () {
        // Could add filtering, sorting, or AJAX refresh features.
      });
    }
  };

})(jQuery, Drupal);
