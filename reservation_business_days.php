<?php
declare(strict_types=1);

/**
 * Returns Japanese public holidays for a year.
 *
 * The Cabinet Office's confirmed 2026/2027 dates take precedence. Other years
 * are calculated from the current Holiday Act rules so reservations do not
 * depend on an external API at runtime.
 *
 * @return array<string, string> Date (Y-m-d) => holiday name
 */
function reservation_japanese_holidays(int $year): array
{
    static $cache = [];
    if (isset($cache[$year])) {
        return $cache[$year];
    }

    $confirmed = [
        2026 => [
            '2026-01-01' => '元日',
            '2026-01-12' => '成人の日',
            '2026-02-11' => '建国記念の日',
            '2026-02-23' => '天皇誕生日',
            '2026-03-20' => '春分の日',
            '2026-04-29' => '昭和の日',
            '2026-05-03' => '憲法記念日',
            '2026-05-04' => 'みどりの日',
            '2026-05-05' => 'こどもの日',
            '2026-05-06' => '振替休日',
            '2026-07-20' => '海の日',
            '2026-08-11' => '山の日',
            '2026-09-21' => '敬老の日',
            '2026-09-22' => '国民の休日',
            '2026-09-23' => '秋分の日',
            '2026-10-12' => 'スポーツの日',
            '2026-11-03' => '文化の日',
            '2026-11-23' => '勤労感謝の日',
        ],
        2027 => [
            '2027-01-01' => '元日',
            '2027-01-11' => '成人の日',
            '2027-02-11' => '建国記念の日',
            '2027-02-23' => '天皇誕生日',
            '2027-03-21' => '春分の日',
            '2027-03-22' => '振替休日',
            '2027-04-29' => '昭和の日',
            '2027-05-03' => '憲法記念日',
            '2027-05-04' => 'みどりの日',
            '2027-05-05' => 'こどもの日',
            '2027-07-19' => '海の日',
            '2027-08-11' => '山の日',
            '2027-09-20' => '敬老の日',
            '2027-09-23' => '秋分の日',
            '2027-10-11' => 'スポーツの日',
            '2027-11-03' => '文化の日',
            '2027-11-23' => '勤労感謝の日',
        ],
    ];
    if (isset($confirmed[$year])) {
        $cache[$year] = $confirmed[$year];
        return $cache[$year];
    }

    $tz = new DateTimeZone('Asia/Tokyo');
    $holidays = [];
    $add = static function (string $date, string $name) use (&$holidays): void {
        $holidays[$date] = $name;
    };
    $nthMonday = static function (int $month, int $nth) use ($year, $tz): string {
        $date = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), $tz);
        while ((int)$date->format('N') !== 1) {
            $date = $date->modify('+1 day');
        }
        return $date->modify('+' . ($nth - 1) . ' weeks')->format('Y-m-d');
    };

    $add(sprintf('%04d-01-01', $year), '元日');
    $add($nthMonday(1, 2), '成人の日');
    $add(sprintf('%04d-02-11', $year), '建国記念の日');
    $add(sprintf('%04d-02-23', $year), '天皇誕生日');

    // Valid for 1980-2099. Confirmed dates above override this calculation.
    $vernalDay = (int)floor(20.8431 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    $autumnalDay = (int)floor(23.2488 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    $add(sprintf('%04d-03-%02d', $year, $vernalDay), '春分の日');
    $add(sprintf('%04d-04-29', $year), '昭和の日');
    $add(sprintf('%04d-05-03', $year), '憲法記念日');
    $add(sprintf('%04d-05-04', $year), 'みどりの日');
    $add(sprintf('%04d-05-05', $year), 'こどもの日');
    $add($nthMonday(7, 3), '海の日');
    $add(sprintf('%04d-08-11', $year), '山の日');
    $add($nthMonday(9, 3), '敬老の日');
    $add(sprintf('%04d-09-%02d', $year, $autumnalDay), '秋分の日');
    $add($nthMonday(10, 2), 'スポーツの日');
    $add(sprintf('%04d-11-03', $year), '文化の日');
    $add(sprintf('%04d-11-23', $year), '勤労感謝の日');

    $baseHolidays = $holidays;

    // A day between two national holidays is also a holiday.
    $cursor = new DateTimeImmutable(sprintf('%04d-01-02', $year), $tz);
    $yearEnd = new DateTimeImmutable(sprintf('%04d-12-30', $year), $tz);
    while ($cursor <= $yearEnd) {
        $date = $cursor->format('Y-m-d');
        if (!isset($baseHolidays[$date])) {
            $previous = $cursor->modify('-1 day')->format('Y-m-d');
            $next = $cursor->modify('+1 day')->format('Y-m-d');
            if (isset($baseHolidays[$previous], $baseHolidays[$next])) {
                $holidays[$date] = '国民の休日';
            }
        }
        $cursor = $cursor->modify('+1 day');
    }

    // A national holiday on Sunday is transferred to the next non-holiday.
    foreach ($baseHolidays as $date => $_name) {
        $holiday = new DateTimeImmutable($date, $tz);
        if ((int)$holiday->format('N') !== 7) {
            continue;
        }
        $substitute = $holiday->modify('+1 day');
        while (isset($holidays[$substitute->format('Y-m-d')])) {
            $substitute = $substitute->modify('+1 day');
        }
        $holidays[$substitute->format('Y-m-d')] = '振替休日';
    }

    ksort($holidays);
    $cache[$year] = $holidays;
    return $cache[$year];
}

function reservation_parse_date(string $value, DateTimeZone $tz): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $tz);
    $errors = DateTimeImmutable::getLastErrors();
    if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return null;
    }
    return $date->format('Y-m-d') === $value ? $date : null;
}

function reservation_closed_date_reason(DateTimeImmutable $date): ?string
{
    $dayOfWeek = (int)$date->format('N');
    if ($dayOfWeek === 6) {
        return '土曜日';
    }
    if ($dayOfWeek === 7) {
        return '日曜日';
    }

    $holidays = reservation_japanese_holidays((int)$date->format('Y'));
    return $holidays[$date->format('Y-m-d')] ?? null;
}

