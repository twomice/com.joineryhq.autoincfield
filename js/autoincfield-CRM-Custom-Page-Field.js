cj(function($) {
  cj('.crm-entity').each(function(){
    var $this = cj(this);
    var customFieldID = $this.find('td:first-child').text();

    CRM.api3('Autoincfield', 'get', {
      "sequential": 1,
      "custom_field_id": customFieldID
    }).then(function(result) {
      if (result[0]) {
        $this.find('td:nth-child(3)').text('Autoincrement').next().text('');
      }
    });
  });
});
