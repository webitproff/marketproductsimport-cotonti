<?php
// English localization
/**
 * Market PRO products Import from CSV/Excel
 * Plugin marketproductsimport for Cotonti 0.9.26, PHP 8.4+
 * Filename: marketproductsimport.en.lang.php
 * Purpose: English language strings
 * Date: Feb 1st, 2026
 * @package marketproductsimport
 * @version 2.0.1
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff
 * @license BSD
 */
defined('COT_CODE') or die('Wrong URL');

/**
 * Plugin Config
 */
$L['cfg_import_table'] = 'Target table';
$L['cfg_import_table_hint'] = 'You must specify <code>market</code> to import data, for example, into cot_market';
$L['cfg_max_rows'] = 'Maximum rows';
$L['cfg_max_rows_hint'] = 'Maximum number of rows to import (0 — no limit)';
$L['cfg_allowed_formats'] = 'Allowed formats';
$L['cfg_allowed_formats_hint'] = 'Comma-separated list of file formats (e.g., xlsx,csv)';

/**
 * Plugin Info
 */
$L['info_title'] = 'Market PRO CSV/Excel Import';
$L['info_desc'] = 'Tool for importing data from CSV/Excel files';
$L['info_notes'] = 'Uses PhpSpreadsheet library version 1.23.0 without Composer. Tested on Cotonti 0.9.26+ with PHP 8.4';

/**
 * Plugin Admin
 */
$L['marketproductsimport_adminTitle'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_adminHelp'] = 'In progress...';

/**
 * Plugin Title & Subtitle
 */
$L['marketproductsimport_title'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_subtitle'] = 'Tool for importing products from Excel files into Market PRO';

/**
 * Plugin Body
 */
$L['marketproductsimport_form_mapping'] = 'Mapping form';
$L['marketproductsimport_form_upload'] = 'Upload form';
$L['marketproductsimport_upload'] = 'Upload file';
$L['marketproductsimport_import'] = 'Start import';
$L['marketproductsimport_progress'] = 'Import progress';
$L['marketproductsimport_select_file'] = 'Select file';
$L['marketproductsimport_max_rows_label'] = 'Maximum number of rows';
$L['marketproductsimport_allowed_formats_label'] = 'Allowed formats';
$L['marketproductsimport_headers'] = 'Detected column headers from your import table';
$L['marketproductsimport_field_table'] = 'Database field';
$L['marketproductsimport_field_excel'] = 'Excel field';
$L['marketproductsimport_import'] = 'Import';
$L['marketproductsimport_reset'] = 'Upload a new file';
