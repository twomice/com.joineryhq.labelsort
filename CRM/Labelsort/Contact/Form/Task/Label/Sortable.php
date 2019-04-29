<?php
/*
 * Copied from CRM_Contact_Form_Task_Label in CiviCRM 5.12.3
 * by Allen Shaw for com.joineryhq.labelsort  Only overridden methods are 
 * included in this class.
 *
 * Mofications are bookended with comments:
 * "BEGIN MODIFICATION for com.joineryhq.labelsort"
 * and
 * "END MODIFICATION for com.joineryhq.labelsort"
 * 
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 */

/**
 * This class helps to print the labels for contacts.
 */
class CRM_Labelsort_Contact_Form_Task_Label_Sortable extends CRM_Contact_Form_Task_Label {

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    parent::preProcess();
  }
  public function getTemplateFileName() {
    return 'CRM/Contact/Form/Task/Label.tpl';
  }
  
  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $fv = $this->controller->exportValues($this->_name);
    $config = CRM_Core_Config::singleton();
    $locName = NULL;
    //get the address format sequence from the config file
    $mailingFormat = Civi::settings()->get('mailing_format');

    $sequence = CRM_Utils_Address::sequence($mailingFormat);

    foreach ($sequence as $v) {
      $address[$v] = 1;
    }

    if (array_key_exists('postal_code', $address)) {
      $address['postal_code_suffix'] = 1;
    }

    //build the returnproperties
    $returnProperties = array('display_name' => 1, 'contact_type' => 1, 'prefix_id' => 1);
    $mailingFormat = Civi::settings()->get('mailing_format');

    $mailingFormatProperties = array();
    if ($mailingFormat) {
      $mailingFormatProperties = CRM_Utils_Token::getReturnProperties($mailingFormat);
      $returnProperties = array_merge($returnProperties, $mailingFormatProperties);
    }
    //we should not consider addressee for data exists, CRM-6025
    if (array_key_exists('addressee', $mailingFormatProperties)) {
      unset($mailingFormatProperties['addressee']);
    }

    $customFormatProperties = array();
    if (stristr($mailingFormat, 'custom_')) {
      foreach ($mailingFormatProperties as $token => $true) {
        if (substr($token, 0, 7) == 'custom_') {
          if (empty($customFormatProperties[$token])) {
            $customFormatProperties[$token] = $mailingFormatProperties[$token];
          }
        }
      }
    }

    if (!empty($customFormatProperties)) {
      $returnProperties = array_merge($returnProperties, $customFormatProperties);
    }

    if (isset($fv['merge_same_address'])) {
      // we need first name/last name for summarising to avoid spillage
      $returnProperties['first_name'] = 1;
      $returnProperties['last_name'] = 1;
    }

    $individualFormat = FALSE;

    /*
     * CRM-8338: replace ids of household members with the id of their household
     * so we can merge labels by household.
     */
    if (isset($fv['merge_same_household'])) {
      $this->mergeContactIdsByHousehold();
      $individualFormat = TRUE;
    }

    //get the contacts information
    $params = array();
    if (!empty($fv['location_type_id'])) {
      $locType = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id');
      $locName = $locType[$fv['location_type_id']];
      $location = array('location' => array("{$locName}" => $address));
      $returnProperties = array_merge($returnProperties, $location);
      $params[] = array('location_type', '=', array(1 => $fv['location_type_id']), 0, 0);
    }
    else {
      $returnProperties = array_merge($returnProperties, $address);
    }

    $rows = array();

    foreach ($this->_contactIds as $key => $contactID) {
      $params[] = array(
        CRM_Core_Form::CB_PREFIX . $contactID,
        '=',
        1,
        0,
        0,
      );
    }

    // fix for CRM-2651
    if (!empty($fv['do_not_mail'])) {
      $params[] = array('do_not_mail', '=', 0, 0, 0);
    }
    // fix for CRM-2613
    $params[] = array('is_deceased', '=', 0, 0, 0);

    $custom = array();
    foreach ($returnProperties as $name => $dontCare) {
      $cfID = CRM_Core_BAO_CustomField::getKeyID($name);
      if ($cfID) {
        $custom[] = $cfID;
      }
    }

    //get the total number of contacts to fetch from database.
    $numberofContacts = count($this->_contactIds);
    $query = new CRM_Contact_BAO_Query($params, $returnProperties);
    $details = $query->apiQuery($params, $returnProperties, NULL, NULL, 0, $numberofContacts);

    $messageToken = CRM_Utils_Token::getTokens($mailingFormat);

    // also get all token values
    CRM_Utils_Hook::tokenValues($details[0],
      $this->_contactIds,
      NULL,
      $messageToken,
      'CRM_Contact_Form_Task_Label'
    );

    $tokens = array();
    CRM_Utils_Hook::tokens($tokens);
    $tokenFields = array();
    foreach ($tokens as $category => $catTokens) {
      foreach ($catTokens as $token => $tokenName) {
        $tokenFields[] = $token;
      }
    }

    foreach ($this->_contactIds as $value) {
      foreach ($custom as $cfID) {
        if (isset($details[0][$value]["custom_{$cfID}"])) {
          $details[0][$value]["custom_{$cfID}"] = CRM_Core_BAO_CustomField::displayValue($details[0][$value]["custom_{$cfID}"], $cfID);
        }
      }
      $contact = CRM_Utils_Array::value($value, $details['0']);

      if (is_a($contact, 'CRM_Core_Error')) {
        return NULL;
      }

      // we need to remove all the "_id"
      unset($contact['contact_id']);

      if ($locName && !empty($contact[$locName])) {
        // If location type is not primary, $contact contains
        // one more array as "$contact[$locName] = array( values... )"

        if (!self::tokenIsFound($contact, $mailingFormatProperties, $tokenFields)) {
          continue;
        }

        $contact = array_merge($contact, $contact[$locName]);
        unset($contact[$locName]);

        if (!empty($contact['county_id'])) {
          unset($contact['county_id']);
        }

        foreach ($contact as $field => $fieldValue) {
          $rows[$value][$field] = $fieldValue;
        }

        $valuesothers = array();
        $paramsothers = array('contact_id' => $value);
        $valuesothers = CRM_Core_BAO_Location::getValues($paramsothers, $valuesothers);
        if (!empty($fv['location_type_id'])) {
          foreach ($valuesothers as $vals) {
            if (CRM_Utils_Array::value('location_type_id', $vals) ==
              CRM_Utils_Array::value('location_type_id', $fv)
            ) {
              foreach ($vals as $k => $v) {
                if (in_array($k, array(
                  'email',
                  'phone',
                  'im',
                  'openid',
                ))) {
                  if ($k == 'im') {
                    $rows[$value][$k] = $v['1']['name'];
                  }
                  else {
                    $rows[$value][$k] = $v['1'][$k];
                  }
                  $rows[$value][$k . '_id'] = $v['1']['id'];
                }
              }
            }
          }
        }
      }
      else {
        if (!self::tokenIsFound($contact, $mailingFormatProperties, $tokenFields)) {
          continue;
        }

        if (!empty($contact['addressee_display'])) {
          $contact['addressee_display'] = trim($contact['addressee_display']);
        }
        if (!empty($contact['addressee'])) {
          $contact['addressee'] = $contact['addressee_display'];
        }

        // now create the rows for generating mailing labels
        foreach ($contact as $field => $fieldValue) {
          $rows[$value][$field] = $fieldValue;
        }
      }
    }

    if (isset($fv['merge_same_address'])) {
      CRM_Core_BAO_Address::mergeSameAddress($rows);
      $individualFormat = TRUE;
    }

    /* *********
     * BEGIN MODIFICATION for com.joineryhq.labelsort.
     */
    // Invoke hook_civicrm_alterMailingLabelRows()
    $null = NULL;
    CRM_Utils_Hook::singleton()->invoke(array('rows', 'formValues'), 
      $rows, $fv, $null,  $null, $null, $null,
      'civicrm_alterMailingLabelRows'
    );
    /*
     * END MODIFICATION for com.joineryhq.labelsort.
     * *********
     */

    // format the addresses according to CIVICRM_ADDRESS_FORMAT (CRM-1327)
    foreach ($rows as $id => $row) {
      if ($commMethods = CRM_Utils_Array::value('preferred_communication_method', $row)) {
        $val = array_filter(explode(CRM_Core_DAO::VALUE_SEPARATOR, $commMethods));
        $comm = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'preferred_communication_method');
        $temp = array();
        foreach ($val as $vals) {
          $temp[] = $comm[$vals];
        }
        $row['preferred_communication_method'] = implode(', ', $temp);
      }
      $row['id'] = $id;
      $formatted = CRM_Utils_Address::format($row, 'mailing_format', FALSE, TRUE, $tokenFields);

      // CRM-2211: UFPDF doesn't have bidi support; use the PECL fribidi package to fix it.
      // On Ubuntu (possibly Debian?) be aware of http://pecl.php.net/bugs/bug.php?id=12366
      // Due to FriBidi peculiarities, this can't be called on
      // a multi-line string, hence the explode+implode approach.
      if (function_exists('fribidi_log2vis')) {
        $lines = explode("\n", $formatted);
        foreach ($lines as $i => $line) {
          $lines[$i] = fribidi_log2vis($line, FRIBIDI_AUTO, FRIBIDI_CHARSET_UTF8);
        }
        $formatted = implode("\n", $lines);
      }
      $rows[$id] = array($formatted);
    }

    //call function to create labels
    self::createLabel($rows, $fv['label_name']);
    CRM_Utils_System::civiExit();
  }

}
