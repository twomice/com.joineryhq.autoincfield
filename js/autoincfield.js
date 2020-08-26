cj(function($) {
  cj(document).ready(function(){
    var $minVal = cj('#min_value').parents('tr');
    $minVal.addClass('crm-custom-field-form-block-min_value autoinc-field').attr('id','autoinc-min-value').hide();
    $minVal.insertAfter('#hideDesc');
    cj('.form-layout-compressed').remove();

    cj('#data_type_0').change(function(){
      if(cj('option:selected', this).text() === 'Autoincrement') {
        cj('#autoinc-min-value').show();
        cj('#hideDefault, #hideDesc, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();
        cj('#is_view').prop('checked', true);
      } else {
        cj('#autoinc-min-value').hide();
        cj('#is_view').prop('checked', false);
        cj('.crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').show();
      }
    });
  });
});
