<?php
// русская локализация

defined('COT_CODE') or die('Wrong URL');

/**
 * Plugin Config
 */
$L['cfg_import_table'] = 'Целевая таблица';
$L['cfg_import_table_hint'] = 'Нужно указать <code>market</code> для импорта данных например, в cot_market';
$L['cfg_max_rows'] = 'Максимум строк';
$L['cfg_max_rows_hint'] = 'Максимальное число строк для импорта (0 — без ограничений)';
$L['cfg_allowed_formats'] = 'Разрешённые форматы';
$L['cfg_allowed_formats_hint'] = 'Список форматов файлов через запятую (например, xlsx,csv)';

/**
 * Plugin Info
 */
$L['info_title'] = 'Market PRO CSV/Excel Import';
$L['info_desc'] = 'Инструмент для импорта данных из CSV/Excel';
$L['info_notes'] = 'Используется библиотека PhpSpreadsheet версии 1.23.0 без Composer. Тестировалось на сайте Cotonti 0.9.26+ под версией PHP 8.4';
/**
 * Plugin Admin
 */
$L['marketproductsimport_adminTitle'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_adminHelp'] = 'В процессе...';

/**
 * Plugin Title & Subtitle
 */
$L['marketproductsimport_title'] = 'Market PRO CSV/Excel Import';
$L['marketproductsimport_subtitle'] = 'Инструмент для импорта товаров из Excel-файлов в Market PRO';

/**
 * Plugin Body
 */

$L['marketproductsimport_form_mapping'] = 'Форма маппинга';
$L['marketproductsimport_form_upload'] = 'Форма загрузки';
$L['marketproductsimport_upload'] = 'Загрузить файл';
$L['marketproductsimport_import'] = 'Начать импорт';
$L['marketproductsimport_progress'] = 'Прогресс импорта';
$L['marketproductsimport_select_file'] = 'Выберите файл';
$L['marketproductsimport_max_rows_label'] = 'Максимальное количество строк';
$L['marketproductsimport_allowed_formats_label'] = 'Допустимые форматы';
$L['marketproductsimport_upload'] = 'Загрузить файл';
$L['marketproductsimport_headers'] = 'Полученные заголовки полей из вашей таблицы импорта';
$L['marketproductsimport_field_table'] = 'Поле в базе данных';
$L['marketproductsimport_field_excel'] = 'Поле в Excel';
$L['marketproductsimport_import'] = 'Импортировать';
$L['marketproductsimport_reset'] = 'Загрузить новый файл'; 