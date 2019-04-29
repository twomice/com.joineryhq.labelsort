(function ($, ts) {
  
  // Add a unique ID to the table holding bhfe fields, so we can access it
  // directly later.
  var first_bhfe_id = CRM.vars.labelsort.bhfe_fields[0];
  $('#' + first_bhfe_id).closest('table').attr('id', 'bhfe-table');
  
  // Move bhfe fields to before price-block. ("bhfe" or "BeforeHookFormElements"
  // fields are added in this extension's buildForm hook.)
  // First create a container to hold these fields, including two separate
  // tbody elements (so two groups of fields can be hidden/displayed independently).
  for (var i in CRM.vars.labelsort.bhfe_fields) {
    $('div.crm-contact-task-mailing-label-form-block table.form-layout-compressed tbody').append(
      $('#' + CRM.vars.labelsort.bhfe_fields[i]).closest('tr')
    );
  }
  
  // Remove the bhfe table, if indeed it's empty (it's conceivable that someone
  // else has added bhfe fields, so we want to be careful not to erase them.
  if ($('table#bhfe-table tr').length == 0) {
    $('table#bhfe-table').remove();
  }
  
})(CRM.$, CRM.ts('com.joineryhq.metrotweaks'));
