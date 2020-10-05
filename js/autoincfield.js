cj(function($) {
  cj(document).ready(function(){
    // Add min value field to the right place and remove original field
    var $minVal = cj('#min_value').parents('tr');
    $minVal.addClass('crm-custom-field-form-block-min_value autoinc-field').attr('id','autoinc-min-value').hide();
    $minVal.insertAfter('#hideDesc');
    cj('.form-layout-compressed').remove();
    cj('#min_value').parent().append('<br /><span class="description"> WARNING: This value can\'t be decreased, e..g if you set it to 10 now, you\'ll never be able to set it to 9 or below.</span>');
    // If autoincrement is selected
    function if_autoincrement() {
      cj('#autoinc-min-value').show();
      cj('#hideDefault, #hideDesc, #data_type_1, .crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').hide();
      cj('#is_view').prop('checked', true);
      cj('input[name="autoinc"]').val(1);
    }

    // If autoinc field has value
    if(cj('input[name="autoinc"]').val()) {

      // If update page
      if(cj('.crm-frozen-field').length) {
        var $newFrozenFieldHtml = cj('.crm-frozen-field').html().replace('Integer', 'Autoincrement').replace('Text', '');
        cj('.crm-frozen-field').html($newFrozenFieldHtml);
      }

      // Select Autoincrement
      cj('#data_type_0 option:selected').text('Autoincrement').trigger('change');
      if_autoincrement();
    }

    // If Autoincrement is selected or not selected
    cj('#data_type_0').change(function(){
      if(cj('option:selected', this).text() === 'Autoincrement') {
        if_autoincrement();
      } else {
        cj('#autoinc-min-value').hide();
        cj('#is_view').prop('checked', false);
        cj('.crm-custom-field-form-block-is_required, .crm-custom-field-form-block-is_view').show();
        cj('input[name="autoinc"]').removeAttr('value');
      }
    });
  });
});
