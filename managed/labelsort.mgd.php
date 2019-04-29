<?php

/**
 * This file registers Labelsort entities via hook_civicrm_managed.
 * Lifecycle events in this extension will cause these registry records to be
 * automatically inserted, updated, or deleted from the database as appropriate.
 * For more details, see "hook_civicrm_managed" (at
 * https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_managed/) as well
 * as "API and the Art of Installation" (at
 * https://civicrm.org/blogs/totten/api-and-art-installation).
 */

return array (
  array (
    'name' => 'CRM_Labelsort_OptionGroup_labelsort',
    'entity' => 'OptionGroup',
    'params' =>
    array (
      'version' => 3,
      "name"=> "labelsort_sort",
      "title"=> "Label Sort",
      "is_reserved"=> "1",
      "is_active"=> "1",
      "is_locked"=> "0",
      "data_type"=> "String",

    ),
  ),
  array (
    'name' => 'CRM_Labelsort_OptionValue_labelsort_name',
    'entity' => 'OptionValue',
    'params' =>
    array (
      'version' => 3,
      "option_group_id"=> "labelsort_sort",
      "label"=> "CiviCRM default sort",
      "value"=> "name",
      "name"=> "default",
      "filter"=> "0",
      "is_default"=> "1",
      "weight"=> "0",
      "is_optgroup"=> "0",
      "is_reserved"=> "1",
      "is_active"=> "1",
    ),
  ),
  array (
    'name' => 'CRM_Labelsort_OptionValue_labelsort_postalcode',
    'entity' => 'OptionValue',
    'params' =>
    array (
      'version' => 3,
      "option_group_id"=> "labelsort_sort",
      "label"=> "Postal Code",
      "value"=> "postalcode",
      "name"=> "postal_code",
      "filter"=> "0",
      "is_default"=> "0",
      "weight"=> "0",
      "is_optgroup"=> "0",
      "is_reserved"=> "1",
      "is_active"=> "1",
    ),
  ),
);
