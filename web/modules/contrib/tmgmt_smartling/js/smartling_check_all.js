(function ($) {
  Drupal.behaviors.smartlingCheckAll = {
    attach: function (context, settings) {
      for (var i = 0; i < settings.smartling.checkAllId.length; ++i) {
        var $checkboxWrapper = $("[id=" + settings.smartling.checkAllId[i] + "]", context);
        var $checkUncheckAll = $checkboxWrapper.find("a.check-all");

        $checkUncheckAll.on('click', function() {
          var $checkboxes = $checkboxWrapper.find('input[type=checkbox]');
          var $checkedCheckboxes = $checkboxWrapper.find('input[type=checkbox]:checked');

          if ($checkboxes.length === $checkedCheckboxes.length) {
              $checkboxes.prop('checked', false);
          } else {
              $checkboxes.prop('checked', true);
          }

          return false;
        });
      }
    }
  };
})(jQuery);
