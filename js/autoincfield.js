cj(function($) {
  cj(document).ready(function(){
    // Add min value field to the right place and remove original field
    var $minVal = cj('#min_value').parents('tr');
    var $isAutoinc = cj('#autoinc').parents('tr');
    $minVal.addClass('crm-custom-field-form-block-min_value autoinc-min-value').attr('id','autoinc-min-value').hide();
    $isAutoinc.addClass('crm-custom-field-form-block-min_value autoinc-field').attr('id','autoinc-field').hide();
    $minVal.insertAfter('#hideDesc');
    $isAutoinc.insertAfter('.crm-custom-field-form-block-data_type');
    cj('.form-layout-compressed').remove();
    cj('#min_value').parent().append('<br /><span class="description"> WARNING: This value can\'t be decreased, e..g if you set it to 10 now, you\'ll never be able to set it to 9 or below.</span>');

    // If edit page
    if(cj('#autoinc').is(':checked')) {
      cj('#autoinc').hide().parent().prepend('<span>[X]</span>');
      cj('#autoinc-field').show();
      cj('#autoinc-min-value').show();
      cj('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();
    }

    // If autoinc field has value
    cj(document).on('click', '#autoinc', function(){
      if(cj('#autoinc').is(':checked')) {
        cj('#autoinc-min-value').show();
        cj('#is_view').prop('checked', true);
        cj('#is_required').prop('checked', false);
        cj('#default_value').val('');
        cj('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();
      } else {
        cj('#autoinc-min-value').hide();
        cj('#is_view').prop('checked', false);
        cj('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').show();
      }
    });

    // If data_type_0 equals to Integer is selected or not selected
    cj('#data_type_0, #data_type_1').change(function(){
      if(cj('#data_type_0 option:selected').text() === 'Integer' && cj('#data_type_1 option:selected').text() === 'Text') {
        cj('#autoinc-field').show();
      } else {
        if(cj('#autoinc').is(':checked')) {
          cj('#autoinc').trigger('click');
        }
        cj('#autoinc-field').hide();
      }
    });
  });
});
