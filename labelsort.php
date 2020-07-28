<?php

require_once 'labelsort.civix.php';
use CRM_Labelsort_ExtensionUtil as E;

/**
 * Implements hook_civicrm_alterMailingLabelRows().
 *
 * @link https://github.com/twomice/com.joineryhq.labelsort/blob/master/README.md
 *
 */
function labelsort_civicrm_alterMailingLabelRows(&$rows, $formValues) {
  // If label_sort isn't defined, there's just no sorting to do. Do nothing and
  // return rows unchanged.
  $sort = CRM_Utils_Array::value('labelsort_sort', $formValues);
  if (empty($sort)) {
    return;
  }

  // Sort by postalcode if called for.
  if ($sort == 'postalcode') {
    uasort($rows, function($a, $b) {
      return strcmp($a['postal_code'], $b['postal_code']);
    });
  }
}

/**
 * Implements hook_civicrm_searchTasks().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_searchTasks().
 */
function labelsort_civicrm_searchTasks($objectType, &$tasks) {
  // For the "Create Mailing Labels" search task, redirect it to our overridden
  // copy of CRM_Labelsort_Contact_Form_Task_Label.
  if (!empty($tasks[CRM_Contact_Task::LABEL_CONTACTS])) {
    $tasks[CRM_Contact_Task::LABEL_CONTACTS] = array(
      'title' => ts('Mailing labels - print'),
      'class' => 'CRM_Labelsort_Contact_Form_Task_Label_Sortable',
      'result' => TRUE,
      'url' => 'civicrm/task/make-mailing-label',
    );
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function labelsort_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Labelsort_Contact_Form_Task_Label_Sortable') {
    // Build Sort options from the "Label Sort" option group.
    $sortOptions = array();
    $result = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => "labelsort_sort",
      'options' => array(
        'sort' => "weight",
        'limit' => 0,
      ),
    ));
    foreach ($result['values'] as $value) {
      $sortOptions[$value['value']] = E::ts($value['label']);
    }
    // Create the "Sort" form field.
    $form->add('select', 'labelsort_sort', ts('Sort by'), $sortOptions);

    // Add "Sort" field to bhfe elements, and assign those fields to the template.
    $tpl = CRM_Core_Smarty::singleton();
    $bhfe = (array) $tpl->get_template_vars('beginHookFormElements');
    $bhfe[] = 'labelsort_sort';
    $form->assign('beginHookFormElements', $bhfe);

    // Tell our JavaScript about our bhfe elements, and add our JavaScript file.
    $vars = array();
    $vars['bhfe_fields'] = $bhfe;
    CRM_Core_Resources::singleton()->addVars('labelsort', $vars);
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.labelsort', 'js/CRM_Contact_Form_Task_Label.js', 100, 'page-footer');
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function labelsort_civicrm_config(&$config) {
  _labelsort_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function labelsort_civicrm_xmlMenu(&$files) {
  _labelsort_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function labelsort_civicrm_install() {
  _labelsort_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function labelsort_civicrm_postInstall() {
  _labelsort_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function labelsort_civicrm_uninstall() {
  _labelsort_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function labelsort_civicrm_enable() {
  _labelsort_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function labelsort_civicrm_disable() {
  _labelsort_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function labelsort_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _labelsort_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function labelsort_civicrm_managed(&$entities) {
  _labelsort_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function labelsort_civicrm_caseTypes(&$caseTypes) {
  _labelsort_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function labelsort_civicrm_angularModules(&$angularModules) {
  _labelsort_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function labelsort_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _labelsort_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function labelsort_civicrm_entityTypes(&$entityTypes) {
  _labelsort_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 */
// function labelsort_civicrm_preProcess($formName, &$form) {

// }

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
// function labelsort_civicrm_navigationMenu(&$menu) {
//   _labelsort_civix_insert_navigation_menu($menu, 'Mailings', array(
//     'label' => E::ts('New subliminal message'),
//     'name' => 'mailing_subliminal_message',
//     'url' => 'civicrm/mailing/subliminal',
//     'permission' => 'access CiviMail',
//     'operator' => 'OR',
//     'separator' => 0,
//   ));
//   _labelsort_civix_navigationMenu($menu);
// }
