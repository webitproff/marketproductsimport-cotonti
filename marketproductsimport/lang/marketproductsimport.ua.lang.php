<?php
// Українська локалізація
/**
 * Market PRO products Import from CSV/Excel
 * Plugin marketproductsimport for Cotonti 0.9.26, PHP 8.4+
 * Filename: marketproductsimport.uk.lang.php
 * Purpose: Ukrainian language strings
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
$L['cfg_import_table'] = 'Цільова таблиця';
$L['cfg_import_table_hint'] = 'Потрібно вказати <code>market</code> для імпорту даних, наприклад, у cot_market';
$L['cfg_max_rows'] = 'Максимальна кількість рядків';
$L['cfg_max_rows_hint'] = 'Максимальна кількість рядків для імпорту (0 — без обмежень)';
$L['cfg_allowed_formats'] = 'Дозволені формати';
$L['cfg_allowed_formats_hint'] = 'Список форматів файлів через кому (наприклад, xlsx,csv)';

/**
 * Plugin Info
 */
$L['info_title'] = 'Market PRO CSV/Excel Import';
$L['info_desc'] = 'Інструмент для імпорту даних із CSV/Excel файлів';
$L['info_notes'] = 'Використовується бібліотека PhpSpreadsheet версії 1.23.0 без Composer. Протестовано на Cotonti 0.9.26+ під PHP 8.4';

/**
 * Plugin Admin
 */
$L['marketproductsimport_adminTitle'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_adminHelp'] = 'У процесі...';

/**
 * Plugin Title & Subtitle
 */
$L['marketproductsimport_title'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_subtitle'] = 'Інструмент для імпорту товарів з Excel-файлів у Market PRO';

/**
 * Plugin Body
 */
$L['marketproductsimport_form_mapping'] = 'Форма мапінгу';
$L['marketproductsimport_form_upload'] = 'Форма завантаження';
$L['marketproductsimport_upload'] = 'Завантажити файл';
$L['marketproductsimport_import'] = 'Розпочати імпорт';
$L['marketproductsimport_progress'] = 'Прогрес імпорту';
$L['marketproductsimport_select_file'] = 'Виберіть файл';
$L['marketproductsimport_max_rows_label'] = 'Максимальна кількість рядків';
$L['marketproductsimport_allowed_formats_label'] = 'Допустимі формати';
$L['marketproductsimport_headers'] = 'Виявлені заголовки колонок з вашої таблиці імпорту';
$L['marketproductsimport_field_table'] = 'Поле бази даних';
$L['marketproductsimport_field_excel'] = 'Поле Excel';
$L['marketproductsimport_import'] = 'Імпортувати';
$L['marketproductsimport_reset'] = 'Завантажити новий файл';
