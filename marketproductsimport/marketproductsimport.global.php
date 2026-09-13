<?php
/**
 * [BEGIN_COT_EXT]
 * Hooks=global
 * [END_COT_EXT]
 */

/**
 * Market PRO products Import from CSV/Excel
 * Plugin marketproductsimport for Cotonti 0.9.26, PHP 8.4+
 * Filename: marketproductsimport.global.php
 * Purpose: Connect to hook "global" in Cotonti Core 
 * Date: Feb 1Th, 2026
 * @package marketproductsimport
 * @version 2.0.1
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff
 * @license BSD
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_langfile('marketproductsimport', 'plug');





// Проверяем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* === AJAX для прогресс-бара === */
if (COT_AJAX && $a == 'progress') {
    $progress = $_SESSION['import_progress'] ?? 0;
    $total = $_SESSION['import_total'] ?? 0;
    $percentage = $total > 0 ? round(($progress / $total) * 100) : 0;
    echo json_encode(['progress' => $percentage]);
    exit;
}