cj(function($) {
  // Find each autoincfield in each custom group
  $('.customFieldGroup').each(function(){
    var fieldGroupEdit = $(this).find('.crm-inline-edit');
    var fieldGroupData = fieldGroupEdit.data('edit-params');

    CRM.api4('CustomField', 'get', {
      where: [["custom_group_id", "=", fieldGroupData.groupID]],
      chain: {"name_me_0":["Autoincfield", "get", {"where":[["custom_field_id", "=", "$id"]]}]}
    }).then(function(customFields) {
      for (var i in customFields) {
        if (customFields[i].name_me_0.length) {
          // Save class and id for autoincfield edit
          fieldGroupEdit.append('<div class="autoincfield-item" data-autoinc-id="' + customFields[i].id + '" data-class=".custom_' + customFields[i].id + '_' + fieldGroupData.customRecId + '-row"></div>');
        }
      }
    });
  });

  $(document).ajaxComplete(function() {
    var contactID = CRM.vars.autoincfield.contactID;
    // When ajax is complete, remove existing edit button
    // so it will not duplicate since clicking the edit button
    // popup is still considered an ajax call
    $('.autoinc-edit-button').remove();

    // Loop each autoincfield details
    $('.autoincfield-item').each(function(){
      var fieldToAppendEdit = $(this).data('class');
      var autoincID = $(this).data('autoinc-id');

      // If custom class exist, add button next to the autoincfield value
      if ($(fieldToAppendEdit).length) {
        $(fieldToAppendEdit).append('<td class="autoinc-edit-button"><a href="/drupal/civicrm/autoincfield?id=' + autoincID + '&cid=' + contactID + '" class="autoinc-edit crm-popup">Edit</a></td>');
      }
    });
  });
});
