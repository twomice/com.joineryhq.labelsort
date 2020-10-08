# com.joineryhq.labelsort: CiviCRM Mailing Label Sort

![Screenshot](/images/labelsort.png)

This CiviCRM extension supports sorting of Mailing Labels by postal code. It also
provides `hook_civicrm_alterMailingLabelRows()`, whereby any extension can
add its own custom sorting algorithm.

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM >= 5.10

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.joineryhq.labelsort@https://github.com/twomice/com.joineryhq.labelsort/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/twomice/com.joineryhq.labelsort.git
cv en labelsort
```

## Usage

Out of the box, this extension provides for sorting of Mailing Labels by either
the current system default (contact.sort_name) or by Postal Code. 

Extension authors wishing to provide additional sort options may do so:
* Add one or more options to the Option Group "labelsort_sort". This Option 
  Group drives the "Sort" field in the Make Mailing Labels form.
* Implement hook_civicrm_alterMailingLabelRows() to provide sort handling for your
  custom sort options. See the implementation in labelsort.php as an example.

## Support
![screenshot](/images/joinery-logo.png)

Joinery provides services for CiviCRM including custom extension development, training, data migrations, and more. We aim to keep this extension in good working order, and will do our best to respond appropriately to issues reported on its [github issue queue](https://github.com/twomice/com.joineryhq.labelsort/issues). In addition, if you require urgent or highly customized improvements to this extension, we may suggest conducting a fee-based project under our standard commercial terms.  In any case, the place to start is the [github issue queue](https://github.com/twomice/com.joineryhq.labelsort/issues) -- let us hear what you need and we'll be glad to help however we can.

And, if you need help with any other aspect of CiviCRM -- from hosting to custom development to strategic consultation and more -- please contact us directly via https://joineryhq.com