cj(function($) {
  cj('.crm-entity').each(function(){
    var $this = cj(this);
    var customFieldID = $this.find('td:first-child').text();

    CRM.api4('Autoincfield', 'get', {
      where: [["custom_field.id", "=", customFieldID]]
    }).then(function(autoincfields) {
      if (autoincfields[0]) {
        $this.find('td:nth-child(3)').text('Integer (Autoincrement)');
      }
    });
  });
});
