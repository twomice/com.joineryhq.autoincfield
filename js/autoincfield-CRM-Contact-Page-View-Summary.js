cj(function($) {
  // Find each autoincfield in each custom group
  $('.customFieldGroup').each(function(){
    var fieldGroupEdit = $(this).find('.crm-inline-edit');
    // Get data param for the api
    var fieldGroupData = fieldGroupEdit.data('edit-params');

    CRM.api4('CustomField', 'get', {
      where: [["custom_group_id", "=", fieldGroupData.groupID]],
      chain: {"name_me_0":["Autoincfield", "get", {"where":[["custom_field_id", "=", "$id"]]}]}
    }).then(function(customFields) {
      for (var i in customFields) {
        // Check if autoinc exist
        if (customFields[i].name_me_0.length) {
          fieldGroupEdit.find('.crm-summary-row').each(function() {
            // Get label to check if its the same as the ajax data label after editing
            var autoincLabel = $('.crm-label', this).text();
            if (customFields[i].label === autoincLabel) {
              // Save class and id for autoincfield edit
              $(this).addClass('custom_' + customFields[i].id + '_' + fieldGroupData.customRecId);
              // Add necessary data for the edit function. It will be used after ajax call
              $('body').append('<div class="autoincfield-item" data-autoinc-id="' + customFields[i].id + '" data-autoinc-rec-id="' + fieldGroupData.customRecId + '" data-field-identifier="custom_' + customFields[i].id + '_' + fieldGroupData.customRecId + '"></div>');
            }
          });
        }
      }
    });
  });

  $(document).ajaxComplete(function(event, request, settings) {
    var contactID = CRM.vars.autoincfield.contactID;
    // When ajax is complete, remove existing edit button
    // so it will not duplicate since clicking the edit button
    // popup is still considered an ajax call
    $('.autoinc-edit-button').remove();

    // Loop each autoincfield details
    $('.autoincfield-item').each(function(){
      var fieldIdentifier = $(this).data('field-identifier');
      var autoincID = $(this).data('autoinc-id');

      // If custom class exist, add button next to the autoincfield value
      if ($('.' + fieldIdentifier + '-row').length) {
        $('.' + fieldIdentifier + '-row').append('<td class="autoinc-edit-button"><a href="/drupal/civicrm/autoincfield?id=' + autoincID + '&cid=' + contactID + '" class="autoinc-edit crm-popup">Edit</a></td>');
      }

      // After closing the edit page of the autoincfield
      // check if target-value class exist and
      // replace old value to the new value
      if ($('.target-value').length && !$('#autoincval').length) {
        // Parse json data to get the new autoincfield value
        var data = JSON.parse('{"' + decodeURI(settings.data.replace(/&/g, "\",\"").replace(/=/g,"\":\"")) + '"}');
        if ($(this).data('autoinc-id') == data.autoincID) {
          $('.crm-summary-row.' + fieldIdentifier).find('.crm-content').text(data.autoincval);
          $('#' + fieldIdentifier).val(data.autoincval);
          var editDisplay = $('.' + fieldIdentifier + '-row .crm-frozen-field').html();
          var oldValueText = $('.' + fieldIdentifier + '-row .crm-frozen-field').text();
          $('.' + fieldIdentifier + '-row .crm-frozen-field').html(editDisplay.replace(oldValueText, data.autoincval));
        }
      }
    });

    // Remove target value after the submit on the edit popup and loop
    if ($('.target-value').length && !$('#autoincval').length) {
      $('.target-value').removeClass('target-value');
    }
  });

  // Detect if autoincfield is edited
  $(document).on('click', '.autoinc-edit-button', function(){
    $(this).parents('.crm-container-snippet').prev().addClass('target-value');
  });
});
