<?php
/**
 * Согласие на использование cookie и необязательных технологий.
 *
 * Обязательные cookie (сохранение самого выбора, сессия админки) ставятся всегда.
 * Аналитические cookie (Яндекс.Метрика) — только после явного согласия.
 * Согласие хранится в cookie COOKIE_CONSENT_NAME в виде JSON и может быть
 * изменено или отозвано в любой момент через «Настройки cookie» в футере.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/** Разобранное состояние согласия. */
function consent_state(): array
{
    static $state = null;
    if (is_array($state)) {
        return $state;
    }

    $state = [
        'set' => false,
        'necessary' => true,
        'analytics' => false,
        'marketing' => false,
        'ts' => '',
        'version' => '',
        'legacy' => false,
    ];

    $raw = $_COOKIE[COOKIE_CONSENT_NAME] ?? '';
    if (is_string($raw) && $raw !== '') {
        $json = json_decode(rawurldecode($raw), true);
        if (is_array($json)) {
            $state['set'] = true;
            $state['necessary'] = true;
            $state['analytics'] = !empty($json['analytics']);
            $state['marketing'] = !empty($json['marketing']);
            $state['ts'] = is_string($json['ts'] ?? null) ? $json['ts'] : '';
            $state['version'] = is_string($json['policy'] ?? null) ? $json['policy'] : '';
        }
    }

    // Миграция с прежнего баннера: «Принять» = согласие на аналитику, «Отклонить» = отказ.
    if (!$state['set']) {
        $legacy = $_COOKIE[COOKIE_LEGACY_NAME] ?? '';
        if ($legacy === '1' || $legacy === '0') {
            $state['set'] = true;
            $state['legacy'] = true;
            $state['analytics'] = $legacy === '1';
            $state['version'] = 'legacy';
        }
    }

    return $state;
}

function consent_is_set(): bool
{
    return (bool) consent_state()['set'];
}

/** Разрешена ли необязательная категория. */
function consent_allows(string $category): bool
{
    $state = consent_state();
    return match ($category) {
        'necessary' => true,
        'analytics' => (bool) $state['analytics'],
        'marketing' => (bool) $state['marketing'],
        default => false,
    };
}

/** Можно ли загружать аналитические счётчики. */
function consent_analytics_allowed(): bool
{
    return consent_allows('analytics') && YANDEX_METRIKA_ID !== '';
}

/** Ссылка на страницу политики cookie. */
function consent_policy_url(): string
{
    return '/cookies';
}
