<?php
/**
 * Market PRO products Import from CSV/Excel
 * Plugin marketproductsimport for Cotonti V.1, PHP 8.5+
 * Filename: marketproductsimport.functions.php
 * Purpose: Functions for the Plugin marketproductsimport
 * Date: Nov 13Th, 2026
 * @package marketproductsimport
 * @version 2.2.0
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/marketproductsimport-cotonti
 * @license BSD
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_langfile('marketproductsimport', 'plug');

$pluginDir = $cfg['plugins_dir'] . '/marketproductsimport';
$libPathPhpSpreadsheet = "$pluginDir/lib/phpspreadsheet/src/PhpOffice/PhpSpreadsheet";
$libPathPsr = "$pluginDir/lib/psr/simple-cache/src/Psr/SimpleCache";
$logFile = "$pluginDir/logs/import.log";

spl_autoload_register(function (string $class) use ($libPathPhpSpreadsheet): void {
    if (str_starts_with($class, 'PhpOffice\\PhpSpreadsheet\\')) {
        $file = $libPathPhpSpreadsheet . '/' . str_replace('\\', '/', substr($class, strlen('PhpOffice\\PhpSpreadsheet\\'))) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

spl_autoload_register(function (string $class) use ($libPathPsr): void {
    if (str_starts_with($class, 'Psr\\SimpleCache\\')) {
        $file = $libPathPsr . '/' . str_replace('\\', '/', substr($class, strlen('Psr\\SimpleCache\\'))) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

require_once "$libPathPhpSpreadsheet/Spreadsheet.php";
require_once "$libPathPhpSpreadsheet/IOFactory.php";


/**
 * Логирование сообщений в файл, если включено в конфиге плагина
 */
function cot_marketproductsimport_log(string $message): void {
    global $cfg, $logFile;
    if (isset($cfg['plugin']['marketproductsimport']['use_function_log']) && $cfg['plugin']['marketproductsimport']['use_function_log'] === '1') {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
    }
}
/**
 * Сокращает длинные строки в массиве для логирования (не меняя исходные данные).
 */
function cot_marketproductsimport_shorten_for_log(array $arr, int $maxLen = 50): array
{
    foreach ($arr as $k => $v) {
        if (is_string($v) && mb_strlen($v) > $maxLen) {
            $arr[$k] = mb_substr($v, 0, $maxLen) . '…[cut ' . (mb_strlen($v) - $maxLen) . ']';
        }
    }
    return $arr;
}

/**
 * Приводит значение из Excel/CSV к типу экстраполя.
 */
function cot_marketproductsimport_parse_extrafield_value(array $exfld, string $value): mixed
{
    $type  = $exfld['field_type'] ?? 'input';
    $value = trim($value);

    $variantsRaw = (string)($exfld['field_variants'] ?? '');
    $variantsRaw = str_replace([' , ', ' ,', ', '], ',', $variantsRaw);
    $variants    = array_map('trim', explode(',', $variantsRaw));

    switch ($type) {
        case 'inputint':
        case 'range':
            return (int)$value;

        case 'currency':
        case 'double':
            return (float)str_replace([' ', ','], ['', '.'], $value);

        case 'checkbox':
            if ($value === '') return 0;
            if (in_array(mb_strtolower($value), ['1', 'yes', 'on', 'true', 'да', 'y', '+'], true)) return 1;
            return is_numeric($value) ? (int)((float)$value ? 1 : 0) : 0;

        case 'datetime':
            if ($value === '') return 0;
            if (is_numeric($value)) return (int)$value;
            $ts = strtotime($value);
            return $ts !== false ? (int)$ts : 0;

        case 'country':
            return mb_strtoupper(mb_substr($value, 0, 2));

        case 'select':
        case 'radio':
            if ($value !== '' && $variantsRaw !== '' && !in_array($value, $variants, true)) {
                cot_marketproductsimport_log("Экстраполе {$exfld['field_name']}: значение '$value' отсутствует в списке вариантов — сохраняем как есть");
            }
            return $value;

        case 'checklistbox':
            if ($value === '') return '';
            $parts = array_map('trim', preg_split('/[,;]+/', $value));
            $valid = array_values(array_intersect($parts, $variants));
            return implode(',', $valid);

        case 'textarea':
            return $value;
        case 'file':
        case 'filesize':
            return $value;

        case 'input':
        default:
            return $value;
    }
}


/**
 * Удаление ссылок из текста, если включено в конфиге
 */
function cot_marketproductsimport_strip_links(string $html): string {
    global $cfg;
    if (!isset($cfg['plugin']['marketproductsimport']['use_function_strip_links']) || $cfg['plugin']['marketproductsimport']['use_function_strip_links'] !== '1') {
        // Если функция отключена — вернуть вход без изменений
        return $html;
    }

    // 1. Удаляем все теги <script> и их содержимое
    $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html);

    // 2. Заменяем <div> на <p>, закрывающие теги тоже
    // Важно: Заменяем именно теги, без содержания
    $html = preg_replace('#<div\b([^>]*)>#i', '<p$1>', $html);
    $html = preg_replace('#</div>#i', '</p>', $html);

    // 3. Удаляем атрибуты событий (onclick, onload и др.) из всех тегов
    // Список атрибутов событий можно расширять по необходимости
    $html = preg_replace('#\s(on\w+)\s*=\s*(["\']).*?\2#i', '', $html);

    // 4. Удаляем javascript: и data: схемы из href/src
    $html = preg_replace_callback('#\s(href|src)\s*=\s*(["\'])(.*?)\2#i', function($m) {
        $attr = $m[1];
        $quote = $m[2];
        $val = trim($m[3]);
        // Удаляем, если начинается с javascript: или data:
        if (preg_match('#^(javascript:|data:)#i', $val)) {
            return '';
        }
        return " $attr=$quote$val$quote";
    }, $html);

    // 5. Удаляем все ссылки <a href=...> оставляя только текст
    $html = preg_replace_callback('#<a\b[^>]*>(.*?)</a>#si', function ($m) {
        // Оставляем только текст без тегов внутри ссылки
        return strip_tags($m[1]);
    }, $html);

    // 6. Удаляем прямые URL вида https://example.com, http://, ftp://, www.
    $html = preg_replace('#\b(?:https?://|ftp://|www\.)\S+\b#i', '', $html);

    // 7. Удаляем пустые теги, например <p></p> или <p> </p> после очистки
    $html = preg_replace('#<(\w+)[^>]*>\s*</\1>#', '', $html);

    // 8. Можно добавить ещё очистка от лишних пробелов и переносов
    $html = trim($html);

    return $html;
}



function cot_marketproductsimport_check_format(string $fileName): string|false {
    global $cfg;
    $allowedFormats = explode(',', $cfg['plugin']['marketproductsimport']['allowed_formats'] ?? 'xlsx,csv');
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedFormats)) {
        cot_marketproductsimport_log("Ошибка: формат '$fileExt' не поддерживается.");
        return false;
    }
    return $fileExt;
}

function cot_marketproductsimport_get_headers(string $filePath, string $fileName): array|string {
    $fileExt = cot_marketproductsimport_check_format($fileName);
    if ($fileExt === false) return "Ошибка: формат не поддерживается.";
    try {
        $readerType = ($fileExt === 'csv') ? 'Csv' : 'Xlsx';
        $reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $headers = [];
        foreach ($spreadsheet->getActiveSheet()->getRowIterator(1)->current()->getCellIterator() as $cell) {
            $value = trim((string) ($cell->getValue() ?? ''));
            $headers[] = $value ?: '0';
        }
        cot_marketproductsimport_log("Считаны заголовки: " . implode(', ', $headers));
        return $headers;
    } catch (Exception $e) {
        $error = "Ошибка чтения заголовков: " . $e->getMessage();
        cot_marketproductsimport_log($error);
        return $error;
    }
}

/**
 * Импорт данных из Excel/CSV в таблицу cot_market.
 *
 * Поддерживает:
 *   - штатные поля cot_market (fieldmrkt_*);
 *   - экстраполя cot_market (ключи маппинга с префиксом page_);
 *   - экстраполя плагина xtradbrowmarket (ключи маппинга с префиксом xtra_),
 *     которые пишутся в отдельную таблицу cot_xtradbrowmarket после того,
 *     как для товара получен fieldmrkt_id.
 *
 * Особенности:
 *   - $db->insert() в Cotonti возвращает bool, поэтому реальный id получаем
 *     через $db->lastInsertId(). Без этого FK cot_xtradbrowmarket.itempagid →
 *     cot_market.fieldmrkt_id падает (Cannot add or update a child row).
 *   - Длинные строки (текст, описание) в логах обрезаются до 250 символов
 *     хелпером cot_marketproductsimport_shorten_for_log().
 *
 * @param string $filePath Путь к файлу Excel/CSV на сервере.
 * @param array  $mapping  Ассоциативный массив маппинга: поле БД => заголовок в Excel.
 * @param string $fileName Имя файла (для проверки расширения).
 *
 * @return string Результат выполнения импорта (успешно/ошибка).
 */
function cot_marketproductsimport_process(string $filePath, array $mapping, string $fileName): string {
    // Подключаем глобальные переменные
    global $db, $cfg, $db_x, $db_market, $sys, $usr, $cot_extrafields;

    // Определяем таблицу для импорта
    $db_market = $db_x . 'market';

    // ------------------------------------------------------------------
    // Карта экстраполей cot_market (ключи с префиксом page_)
    // ------------------------------------------------------------------
    $cot_extrafields = Cot::$extrafields;
    if (empty($cot_extrafields[$db_market]) || !is_array($cot_extrafields[$db_market])) {
        $cot_extrafields[$db_market] = [];
    }
    $extraFieldsByName = [];
    foreach ($cot_extrafields[$db_market] as $exfld) {
        $extraFieldsByName['page_' . $exfld['field_name']] = $exfld;
    }
    cot_marketproductsimport_log(
        "Экстраполя cot_market: " .
        (empty($extraFieldsByName) ? 'нет' : implode(', ', array_keys($extraFieldsByName)))
    );

    // ------------------------------------------------------------------
    // Карта экстраполей cot_xtradbrowmarket (ключи с префиксом xtra_)
    // ------------------------------------------------------------------
    // Подключается только если активен плагин xtradbrowmarket.
    $xtraTable = $db_x . 'xtradbrowmarket';
    $xtraExtrafieldsByName = [];
    if (cot_plugin_active('xtradbrowmarket')
        && !empty($cot_extrafields[$xtraTable])
        && is_array($cot_extrafields[$xtraTable])) {
        foreach ($cot_extrafields[$xtraTable] as $exfld) {
            $xtraExtrafieldsByName['xtra_' . $exfld['field_name']] = $exfld;
        }
    }
    cot_marketproductsimport_log(
        "Экстраполя xtradbrowmarket: " .
        (empty($xtraExtrafieldsByName) ? 'нет' : implode(', ', array_keys($xtraExtrafieldsByName)))
    );

    // Проверяем формат файла
    $fileExt = cot_marketproductsimport_check_format($fileName);
    if ($fileExt === false) return "Ошибка: формат не поддерживается.";

    try {
        // Определяем существующие колонки в таблице cot_market
        $existingColumns = [];
        $colsRes = $db->query("SHOW COLUMNS FROM `$db_market`")->fetchAll();
        foreach ($colsRes as $col) {
            $existingColumns[] = $col['Field'];
        }
        cot_marketproductsimport_log("Найденные поля таблицы: " . implode(', ', $existingColumns));

        // Определяем тип ридера (CSV или XLSX)
        $readerType = ($fileExt === 'csv') ? 'Csv' : 'Xlsx';

        // Создаём объект ридера
        $reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader($readerType);

        // Читаем только данные (без стилей)
        $reader->setReadDataOnly(true);

        // Загружаем файл
        $spreadsheet = $reader->load($filePath);

        // Получаем активный лист
        $sheet = $spreadsheet->getActiveSheet();

        // Определяем количество строк (без строки заголовков)
        $totalRows = $sheet->getHighestRow() - 1;

        // Лимит строк для импорта (0 — без ограничений)
        $maxRows = (int) ($cfg['plugin']['marketproductsimport']['max_rows'] ?? 100);

        // Сбрасываем прогресс в сессии
        $_SESSION['import_progress'] = 0;
        $_SESSION['import_total']    = min($totalRows, $maxRows > 0 ? $maxRows : $totalRows);

        // Счётчик импортированных строк cot_market
        $importedRows = 0;

        // Счётчик записей, сохранённых в cot_xtradbrowmarket
        $xtraSavedRows = 0;

        // Получаем заголовки столбцов
        $headers = cot_marketproductsimport_get_headers($filePath, $fileName);
        if (!is_array($headers)) return $headers;

        // Логируем маппинг
        cot_marketproductsimport_log("Маппинг: " . json_encode($mapping));

        // Перебираем строки в файле
        foreach ($sheet->getRowIterator() as $row) {
            // Пропускаем строку заголовка и строки за пределом лимита
            if ($row->getRowIndex() === 1 || ($maxRows > 0 && $importedRows >= $maxRows)) continue;

            // Массив значений текущей строки
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $value = $cell->getValue();

                // Если это дата Excel — конвертируем в timestamp
                if ($value instanceof \PhpOffice\PhpSpreadsheet\Shared\Date) {
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
                }

                $data[] = trim((string) $value);
            }
            cot_marketproductsimport_log(str_repeat('=', 100));
            // Логируем строку с обрезкой длинных значений до 250 символов
            cot_marketproductsimport_log(
                "Обрабатываемая строка {$row->getRowIndex()}: " .
                json_encode(cot_marketproductsimport_shorten_for_log($data))
            );

            // Массив записи для cot_market
            $record = [];

            // Отдельный массив для cot_xtradbrowmarket
            $xtraRecord = [];

            // Значения по умолчанию для штатных полей cot_market
            $defaults = [
                'fieldmrkt_id'        => null,
                'fieldmrkt_pcod'      => '',
                'fieldmrkt_alias'     => '',
                'fieldmrkt_state'     => 0,
                'fieldmrkt_cat'       => '',
                'fieldmrkt_title'     => '',
                'fieldmrkt_desc'      => '',
                'fieldmrkt_metatitle' => '',
                'fieldmrkt_metah1'    => '',
                'fieldmrkt_metadesc'  => '',
                'fieldmrkt_text'      => '',
                'fieldmrkt_costdflt'  => 0.00,
                'fieldmrkt_cost_usd'  => 0.00,
                'fieldmrkt_parser'    => 'html',
                'fieldmrkt_ownerid'   => $usr['id'] ?: 1,
                'fieldmrkt_date'      => (int) Cot::$sys['now'],
                'fieldmrkt_updated'   => (int) Cot::$sys['now'],
                'fieldmrkt_count'     => 0,
            ];

            // Добавляем дефолтные значения для всех экстраполей cot_market.
            // field_default прогоняем через тот же парсер, что и значения из Excel,
            // чтобы '' не попала в INT-колонку в strict mode.
            foreach ($extraFieldsByName as $exfldName => $exfld) {
                if (!isset($defaults[$exfldName])) {
                    $defaults[$exfldName] = cot_marketproductsimport_parse_extrafield_value(
                        $exfld,
                        (string) ($exfld['field_default'] ?? '')
                    );
                }
            }

            // Заполняем только те поля, которые реально есть в cot_market
            foreach ($defaults as $field => $defValue) {
                if (in_array($field, $existingColumns)) {
                    $record[$field] = $defValue;
                }
            }

            // ------------------------------------------------------------------
            // Разбор маппинга: раскладываем значения по трём «корзинам»:
            //   1) xtra_* — экстраполя xtradbrowmarket   → $xtraRecord
            //   2) page_* / штатные поля cot_market      → $record
            //   3) остальное (нет в таблице)             → пропускаем
            // ------------------------------------------------------------------
            foreach ($mapping as $dbField => $excelHeader) {
                // Пропускаем пустой маппинг ('0' = «не импортировать»)
                if (!$excelHeader || $excelHeader === '0') {
                    continue;
                }
                // Ищем индекс колонки в заголовках файла
                if (($colIndex = array_search($excelHeader, $headers)) === false) {
                    continue;
                }

                // Берём значение ячейки
                $value = $data[$colIndex] ?? '';

                // Экстраполе xtradbrowmarket — сохраняем под реальным именем
                // колонки (без префикса xtra_), т.к. xtradbrowmarket_save
                // работает с именами колонок как есть.
                if (isset($xtraExtrafieldsByName[$dbField])) {
                    $exfld = $xtraExtrafieldsByName[$dbField];
                    $xtraRecord[$exfld['field_name']] = cot_marketproductsimport_parse_extrafield_value(
                        $exfld,
                        (string) $value
                    );
                    cot_marketproductsimport_log(
                        "Xtra $dbField ({$exfld['field_type']}): исходное '$value', результат '{$xtraRecord[$exfld['field_name']]}'"
                    );
                    continue;
                }

                // Поле не существует в cot_market — пропускаем
                if (!in_array($dbField, $existingColumns)) {
                    continue;
                }

                // Экстраполе cot_market — обрабатываем с учётом его field_type
                if (isset($extraFieldsByName[$dbField])) {
                    $exfld = $extraFieldsByName[$dbField];
                    $record[$dbField] = cot_marketproductsimport_parse_extrafield_value($exfld, (string) $value);
                    cot_marketproductsimport_log(
                        "Экстраполе $dbField ({$exfld['field_type']}): исходное '$value', результат '{$record[$dbField]}'"
                    );

                // Если поле — дата
                } elseif (in_array($dbField, ['fieldmrkt_date', 'fieldmrkt_updated'])) {
                    $timestamp = is_numeric($value) ? (int)$value : strtotime((string)$value);
                    $record[$dbField] = $timestamp ?: (int) Cot::$sys['now'];
                    cot_marketproductsimport_log("Дата для $dbField: исходное '$value', результат {$record[$dbField]}");

                // Если поле — цена
                } elseif (in_array($dbField, ['fieldmrkt_costdflt', 'fieldmrkt_cost_usd'])) {
                    $value = str_replace(',', '.', (string)$value);
                    $integerPart = (int)explode('.', $value)[0];
                    $record[$dbField] = number_format($integerPart, 2, '.', '');
                    cot_marketproductsimport_log("Цена для $dbField: исходное '$value', результат {$record[$dbField]}");
					
                } elseif (in_array($dbField, ['fieldmrkt_state', 'fieldmrkt_count', 'fieldmrkt_ownerid'])) {
                    $record[$dbField] = ($value === '') ? 0 : (int)$value;

                // Текстовое поле — удаляем ссылки
                } elseif (in_array($dbField, ['fieldmrkt_text', 'fieldmrkt_desc', 'fieldmrkt_metadesc'])) {
                    $record[$dbField] = cot_marketproductsimport_strip_links((string)$value);

                // Все остальные поля — сохраняем как строку
                } else {
                    $record[$dbField] = (string)$value;
                }
            }

            // Проверка обязательных полей (категория и заголовок)
            if (
                (in_array('fieldmrkt_cat',   $existingColumns) && trim((string)($record['fieldmrkt_cat']   ?? '')) === '') ||
                (in_array('fieldmrkt_title', $existingColumns) && trim((string)($record['fieldmrkt_title'] ?? '')) === '')
            ) {
                cot_marketproductsimport_log("Строка {$row->getRowIndex()} пропущена: пустые fieldmrkt_cat или fieldmrkt_title");
                continue;
            }

            // Если fieldmrkt_text пустой — подставляем заголовок
            if (in_array('fieldmrkt_text', $existingColumns) && empty($record['fieldmrkt_text']) && !empty($record['fieldmrkt_title'])) {
                $record['fieldmrkt_text'] = $record['fieldmrkt_title'];
                cot_marketproductsimport_log("fieldmrkt_text пуст — подставлен заголовок: {$record['fieldmrkt_title']}");
            }

            $varcharLimits = [
                'fieldmrkt_pcod'      => 64,
                'fieldmrkt_alias'     => 255,
                'fieldmrkt_cat'       => 255,
                'fieldmrkt_title'     => 255,
                'fieldmrkt_desc'      => 255,
                'fieldmrkt_metatitle' => 255,
                'fieldmrkt_metah1'    => 255,
                'fieldmrkt_metadesc'  => 255,
                'fieldmrkt_parser'    => 64,
            ];
            foreach ($varcharLimits as $vField => $vMax) {
                if (isset($record[$vField]) && is_string($record[$vField]) && mb_strlen($record[$vField]) > $vMax) {
                    cot_marketproductsimport_log("Обрезка $vField: было " . mb_strlen($record[$vField]) . ", стало $vMax");
                    $record[$vField] = mb_substr($record[$vField], 0, $vMax);
                }
            }

            // Логируем подготовленные массивы (длинные строки обрезаются до 250 символов)
            cot_marketproductsimport_log(
                "Подготовленный массив cot_market: " .
                json_encode(cot_marketproductsimport_shorten_for_log($record))
            );
			
            if (!empty($xtraRecord)) {
                cot_marketproductsimport_log(
                    "Подготовленный массив cot_xtradbrowmarket: " .
                    json_encode(cot_marketproductsimport_shorten_for_log($xtraRecord))
                );
            }

            try {
                // $db->insert() в Cotonti возвращает bool (true/false), а не id.
                // Реальный fieldmrkt_id получаем через lastInsertId().
                $newMarketId = 0;
                if (!empty($record) && $db->insert($db_market, $record)) {
                    $newMarketId = (int)$db->lastInsertId();
                }

                if ($newMarketId > 0) {
                    $importedRows++;
                    $_SESSION['import_progress'] = $importedRows;
                    cot_marketproductsimport_log(
                        "Импортирована строка {$row->getRowIndex()} (fieldmrkt_id={$newMarketId})"
                    );

                    // Сохранение экстраполей cot_xtradbrowmarket.
                    // Делаем это СТРОГО после получения fieldmrkt_id, потому что
                    // таблица cot_xtradbrowmarket имеет FK itempagid → cot_market.fieldmrkt_id
                    // с ON DELETE CASCADE. Если передать неверный id — получим
                    // «Cannot add or update a child row».
                    if (!empty($xtraRecord) && cot_plugin_active('xtradbrowmarket')) {
                        try {
                            if (function_exists('xtradbrowmarket_save')) {
                                // Родная функция плагина: сама сделает INSERT или UPDATE
                                xtradbrowmarket_save($newMarketId, $xtraRecord);
                                $xtraSavedRows++;
                                cot_marketproductsimport_log(
                                    "Xtra сохранены (xtradbrowmarket_save) fieldmrkt_id={$newMarketId}: " .
                                    json_encode(cot_marketproductsimport_shorten_for_log($xtraRecord))
                                );
                            } else {
                                // Fallback — прямой INSERT, если функция недоступна
                                $xtraRecord['itempagid'] = $newMarketId;
                                $db->insert($xtraTable, $xtraRecord);
                                $xtraSavedRows++;
                                cot_marketproductsimport_log(
                                    "Xtra сохранены (fallback INSERT) fieldmrkt_id={$newMarketId}: " .
                                    json_encode(cot_marketproductsimport_shorten_for_log($xtraRecord))
                                );
                            }
                        } catch (Exception $xe) {
                            cot_marketproductsimport_log(
                                "Ошибка сохранения xtradbrowmarket fieldmrkt_id={$newMarketId}: " .
                                $xe->getMessage() .
                                " | data=" . json_encode(cot_marketproductsimport_shorten_for_log($xtraRecord))
                            );
                        }
                    }
                } else {
                    cot_marketproductsimport_log(
                        "Ошибка вставки строки {$row->getRowIndex()}: не удалось добавить запись"
                    );
                }
            } catch (Exception $e) {
                cot_marketproductsimport_log(
                    "Исключение при вставке строки {$row->getRowIndex()}: " . $e->getMessage()
                );
            }
        }

        // Финальный результат
        $result = "Импорт завершён. Добавлено записей: $importedRows из $totalRows. Записей в xtradbrowmarket: $xtraSavedRows";
        cot_marketproductsimport_log($result);

        // Чистим сессию
        unset($_SESSION['import_progress'], $_SESSION['import_total']);

        return $result;

    } catch (Exception $e) {
        $error = "Ошибка импорта: " . $e->getMessage();
        cot_marketproductsimport_log($error);
        return $error;
    }
}
