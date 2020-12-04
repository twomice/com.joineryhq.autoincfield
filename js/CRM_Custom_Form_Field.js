CRM.$(function($) {
  $(document).ready(function(){
    // Add min value field and autoinc checkbox to the right place and remove original field
    var $minVal = $('#min_value').parents('tr');
    var $isAutoinc = $('#autoinc').parents('tr');
    $minVal.addClass('crm-custom-field-form-block-min_value autoinc-min-value').attr('id','autoinc-min-value').hide();
    $isAutoinc.addClass('crm-custom-field-form-block-min_value autoinc-field').attr('id','autoinc-field').hide();
    $minVal.insertAfter('#hideDesc');
    $isAutoinc.insertAfter('.crm-custom-field-form-block-data_type');
    $('.form-layout-compressed').remove();

    // Add min value field description
    $('#min_value').parent().append('<br /><span class="description"> WARNING: This value can\'t be decreased, e..g if you set it to 10 now, you\'ll never be able to set it to 9 or below.</span>');

    // If edit page
    if($('#autoinc').is(':checked')) {
      // Replace autoinc checkbox with check mark
      $('#autoinc').hide().parent().prepend('<span>[X]</span>');

      // Show and hide fields
      $('#autoinc-field').show();
      $('#autoinc-min-value').show();
      $('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();

      // Add Autoincrement text next to Integer if autoinc field is checked
      var $dataTypeField = $('.crm-custom-field-form-block-data_type .crm-frozen-field');
      $dataTypeField.html($dataTypeField.html().replace('Integer','Integer (Autoincrement)'));
    }

    // Show autoincfield if data type matches
    if($('#data_type_0').val() == 1 && $('#data_type_1').val() === 'Text') {
      $('#autoinc-field').show();
    }

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

    // Match data_type_0 and data_type_1
    $('#data_type_0, #data_type_1').change(function(){
      if($('#data_type_0 option:selected').text() === 'Integer' && $('#data_type_1 option:selected').text() === 'Text') {
        $('#autoinc-field').show();
      } else {
        if($('#autoinc').is(':checked')) {
          $('#autoinc').trigger('click');
        }
        $('#autoinc-field').hide();
      }
    });
  });
});
