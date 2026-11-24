<?php
/**
 * Выход из админки: /upravlenie/logout
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

admin_logout();
header('Location: /upravlenie/login', true, 303);
exit;
