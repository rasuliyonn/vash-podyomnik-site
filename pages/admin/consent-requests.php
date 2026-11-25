<?php
/**
 * Обращения субъектов ПД: /upravlenie/consent-requests
 * Отзыв согласия, доступ, уточнение, удаление, отказ от рекламы.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

require_admin();

$notice = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        http_response_code(400);
        exit('Неверный CSRF-токен');
    }
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($action === 'status') {
            consent_request_set_status($id, (string) ($_POST['status'] ?? 'new'));
            $notice = 'Статус обращения обновлён.';
        } elseif ($action === 'delete') {
            consent_request_delete($id);
            $notice = 'Обращение удалено.';
        }
    }
}

$requests = consent_requests_all();
$kinds = [
    'withdraw' => 'Отзыв согласия',
    'marketing' => 'Отказ от рекламы',
    'access' => 'Доступ к данным',
    'correct' => 'Уточнение данных',
    'delete' => 'Удаление данных',
    'other' => 'Другое',
];
$statuses = ['new' => 'Новое', 'processing' => 'В работе', 'done' => 'Обработано'];

admin_page_start('Обращения по персональным данным');
?>
<?php if ($notice !== ''): ?><p class="admin-ok"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error !== ''): ?><p class="admin-error-box"><?= e($error) ?></p><?php endif; ?>

<h2 class="admin-list-header">Обращения по персональным данным (<?= count($requests) ?>)</h2>
<p class="admin-hint">
  Срок ответа — 10 рабочих дней (ч. 1 ст. 20 152-ФЗ). Отзыв согласия: прекратить обработку и уничтожить данные,
  обрабатываемые только на основании согласия, — в течение 30 дней (ч. 5 ст. 21 152-ФЗ). Отказ от рекламы —
  в течение 3 рабочих дней (ч. 1 ст. 18 38-ФЗ).
</p>

<?php if (!$requests): ?>
<p class="admin-empty">Обращений нет.</p>
<?php else: ?>
<div class="admin-table-wrap">
<table class="admin-table">
  <thead>
    <tr><th>#</th><th>Дата</th><th>Тип</th><th>Контакт</th><th>Сообщение</th><th>Статус</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($requests as $request): ?>
    <tr>
      <td><?= (int) $request['id'] ?></td>
      <td><?= e((string) $request['created_at']) ?><br><small><?= e((string) ($request['ip_truncated'] ?? '')) ?></small></td>
      <td><?= e($kinds[$request['kind']] ?? $request['kind']) ?></td>
      <td><?= e((string) $request['contact']) ?></td>
      <td style="max-width: 360px"><?= nl2br(e((string) $request['message'])) ?></td>
      <td>
        <form method="post" action="/upravlenie/consent-requests" style="margin: 0">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
          <select name="status" class="status-select <?= $request['status'] === 'new' ? 'yellow' : ($request['status'] === 'done' ? 'green' : '') ?>" onchange="this.form.submit()">
            <?php foreach ($statuses as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $request['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <?php if (($request['processed_at'] ?? '') !== ''): ?><small>обработано <?= e((string) $request['processed_at']) ?></small><?php endif; ?>
      </td>
      <td>
        <form method="post" action="/upravlenie/consent-requests" style="margin: 0" onsubmit="return confirm('Удалить обращение #<?= (int) $request['id'] ?>?');">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
          <button type="submit" class="admin-action-btn danger">Удалить</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<section class="admin-section" style="margin-top: 32px">
  <h2 class="admin-list-header">Порядок действий</h2>
  <ol class="admin-hint" style="line-height: 1.9">
    <li>Найдите заявку по телефону/email в разделе «Заявки» (есть поиск).</li>
    <li>Отзыв согласия: удалите заявку (вместе с ней удаляются записи журнала согласия), отправьте субъекту подтверждение.</li>
    <li>Отказ от рекламы: прекратите рассылку, поставьте статус «Обработано».</li>
    <li>Запрос доступа: подготовьте сведения об обработке по ст. 14 152-ФЗ и направьте на контакт субъекта.</li>
    <li>Удалите обращение после истечения срока хранения (3 года) или сразу после обработки, если оно не нужно для подтверждения.</li>
  </ol>
</section>
<?php
admin_page_end();
