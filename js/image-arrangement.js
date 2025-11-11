/**
 * @file
 * JavaScript for the image arrangement page with tabledrag.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.betaTenderImageArrangement = {
    attach: function (context, settings) {
      // Initialize tabledrag functionality.
      $('#image-arrangement-table', context).once('betaTenderImageArrangement').each(function () {
        // Tabledrag is already initialized by core/drupal.tabledrag library.
        // Add any custom enhancements here if needed.
        
        // Update checkbox visibility based on parent status.
        $(this).find('tr').each(function () {
          var $row = $(this);
          var $parentSelect = $row.find('.image-parent');
          var $checkbox = $row.find('input[type="checkbox"]');
          
          // If this row has a parent selected, hide the checkbox.
          if ($parentSelect.val() !== '') {
            $checkbox.prop('disabled', true).closest('td').hide();
          }
        });
        
        // Listen for changes in parent selection.
        $(this).find('.image-parent').on('change', function () {
          var $parentSelect = $(this);
          var $row = $parentSelect.closest('tr');
          var $checkbox = $row.find('input[type="checkbox"]');
          
          if ($parentSelect.val() !== '') {
            $checkbox.prop('disabled', true).prop('checked', false).closest('td').hide();
          } else {
            $checkbox.prop('disabled', false).closest('td').show();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
