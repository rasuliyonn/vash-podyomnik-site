<?php
/**
 * Выход из админки: /admin/logout
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

admin_logout();
header('Location: /admin/login', true, 303);
exit;
