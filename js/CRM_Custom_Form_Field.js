CRM.$(function($) {
  $(document).ready(function(){
    // Add min value field and autoinc checkbox to the right place and remove original field
    var $minVal = $('#min_value').parents('tr');
    var $isAutoinc = $('#autoinc').parents('tr');
    $minVal.addClass('crm-custom-field-form-block-min_value autoinc-min-value').attr('id','autoinc-min-value').hide();
    $isAutoinc.addClass('crm-custom-field-form-block-min_value autoinc-field').attr('id','autoinc-field').hide();
    $minVal.insertAfter('#hideDesc');
    var insertIsAutoinc = '.crm-custom-field-form-block-data_type';
    $('.form-layout-compressed').remove();

    // Add min value field description
    $('#min_value').parent().append('<br /><span class="description">' + ts('WARNING: This value can\'t be decreased, e..g if you set it to 10 now, you\'ll never be able to set it to 9 or below.') + '</span>');

    var dataType = '#data_type_0';
    var htmlType = '#data_type_1';

    // If civicrm v5.32.2
    if ($('#html_type').length) {
      insertIsAutoinc = '.crm-custom-field-form-block-html_type';
      dataType = '#data_type';
      htmlType = '#html_type';
    }

    $isAutoinc.insertAfter(insertIsAutoinc);

    // If edit page
    if($('#autoinc').is(':checked')) {
      // Replace autoinc checkbox with check mark
      $('#autoinc').hide().parent().prepend('<span>[X]</span>');

      // Show and hide fields
      $('#autoinc-field').show();
      $('#autoinc-min-value').show();
      $('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();

      // If civicrm v5.32.2
      if ($('#html_type').length) {
        $(htmlType).attr('disabled', 'disabled');
      }

      // Add Autoincrement text next to Integer if autoinc field is checked
      var $dataTypeField = $('.crm-custom-field-form-block-data_type .crm-frozen-field');
      $dataTypeField.html($dataTypeField.html().replace('Integer', ts('Integer (Autoincrement)')));
    }

    // Show autoincfield if data type matches
    if($(dataType).val() === 1 && $(htmlType).val() === 'Text') {
      $('#autoinc-field').show();
    }

    // Match data_type and html_type
    $(document).on('change', dataType + ', ' + htmlType, function(){
      if($(dataType + ' option:selected').text() === 'Integer' && $(htmlType + ' option:selected').val() === 'Text') {
        $('#autoinc-field').show();
      } else {
        if($('#autoinc').is(':checked')) {
          $('#autoinc').trigger('click');
        }
        $('#autoinc-field').hide();
      }
    });

    // If autoinc field has value
    $(document).on('click', '#autoinc', function(){
      if($('#autoinc').is(':checked')) {
        $('#autoinc-min-value').show();
        $('#is_view').prop('checked', true);
        $('#is_required').prop('checked', false);
        $('#default_value').val('');
        $('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();
      } else {
        $('#autoinc-min-value').hide();
        $('#is_view').prop('checked', false);
        $('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').show();
      }
    });
  });
});
