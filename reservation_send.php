<?php
declare(strict_types=1);

const GAS_WEB_APP_URL = 'https://script.google.com/macros/s/AKfycbyHdtQd3n8BMtpWP9AdOU3ZRphDdTOq24INZkVxWEqOz2_hPSOneYBsfzWAdFIgV0ft/exec';

function reservation_redirect_error(string $message): void
{
    header('Location: reservation.php?error=' . rawurlencode($message), true, 303);
    exit;
}

function reservation_value(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function reservation_post_to_gas(array $payload): array
{
    $body = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init(GAS_WEB_APP_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false) {
            return ['ok' => false, 'message' => $error ?: '予約受付システムへの接続に失敗しました。', 'status' => $status];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);
        $response = file_get_contents(GAS_WEB_APP_URL, false, $context);
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $m)) {
                    $status = (int)$m[1];
                    break;
                }
            }
        }
        if ($response === false) {
            return ['ok' => false, 'message' => '予約受付システムへの接続に失敗しました。', 'status' => $status];
        }
    }

    $decoded = json_decode((string)$response, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'message' => '予約受付システムの応答を確認できませんでした。', 'status' => $status];
    }
    $decoded['status'] = $status;
    return $decoded;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservation.php', true, 303);
    exit;
}

if (reservation_value('website') !== '') {
    reservation_redirect_error('送信内容を確認できませんでした。');
}

$preferredDate1 = reservation_value('preferred_date_1');
$preferredDate2 = reservation_value('preferred_date_2');
$preferredDate3 = reservation_value('preferred_date_3');
$timeSlot = reservation_value('time_slot');
$meetingType = reservation_value('meeting_type');
$serviceType = reservation_value('service_type');
$company = reservation_value('company');
$customerName = reservation_value('customer_name');
$customerEmail = reservation_value('customer_email');
$customerPhone = reservation_value('customer_phone');
$message = reservation_value('message');
$privacy = reservation_value('privacy');

if ($preferredDate1 === '' || $timeSlot === '' || $meetingType === '' || $serviceType === '' || $customerName === '' || $customerEmail === '' || $customerPhone === '' || $privacy !== '1') {
    reservation_redirect_error('必須項目を入力してください。');
}

if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    reservation_redirect_error('メールアドレスを確認してください。');
}

$tz = new DateTimeZone('Asia/Tokyo');
$today = new DateTimeImmutable('today', $tz);
$dateCandidates = [
    ['label' => '第一希望', 'value' => $preferredDate1, 'required' => true],
    ['label' => '第二希望', 'value' => $preferredDate2, 'required' => false],
    ['label' => '第三希望', 'value' => $preferredDate3, 'required' => false],
];
$dateLines = [];
$primaryDate = '';
$seenDates = [];
foreach ($dateCandidates as $c) {
    $d = trim($c['value']);
    if ($d === '') {
        if ($c['required']) {
            reservation_redirect_error('第一希望日を入力してください。');
        }
        continue;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        reservation_redirect_error($c['label'] . '日を確認してください。');
    }
    $candidate = new DateTimeImmutable($d . ' 00:00:00', $tz);
    if ($candidate < $today) {
        reservation_redirect_error($c['label'] . '日は本日以降を選択してください。');
    }
    if (in_array($d, $seenDates, true)) {
        reservation_redirect_error('希望日が重複しています。別の日を選んでください。');
    }
    $seenDates[] = $d;
    $dateLines[] = $c['label'] . ': ' . $d;
    if ($primaryDate === '') {
        $primaryDate = $d;
    }
}
if ($primaryDate === '') {
    reservation_redirect_error('第一希望日を入力してください。');
}

$slotParts = explode('|', $timeSlot);
if (count($slotParts) !== 2 || !preg_match('/^\d{2}:\d{2}$/', $slotParts[0]) || !preg_match('/^\d{2}:\d{2}$/', $slotParts[1])) {
    reservation_redirect_error('希望時間を確認してください。');
}

$preferredStart = $primaryDate . 'T' . $slotParts[0] . ':00+09:00';
$preferredEnd = $primaryDate . 'T' . $slotParts[1] . ':00+09:00';
$datesLabel = implode(' / ', $seenDates);
$subject = '予約希望: ' . $datesLabel . ' ' . $slotParts[0] . '-' . $slotParts[1] . ' ' . $meetingType;
$requestBody = implode("\n", array_filter(array_merge(
    $dateLines,
    [
        '希望時間: ' . $slotParts[0] . '-' . $slotParts[1],
        '相談方法: ' . $meetingType,
        '相談内容: ' . $serviceType,
        $company !== '' ? '会社名・屋号: ' . $company : '',
        $message !== '' ? '補足: ' . $message : '補足: なし',
    ]
)));

$result = reservation_post_to_gas([
    'action' => 'submit_ticket',
    'type' => 'Reservation',
    'subject' => $subject,
    'customerName' => $customerName,
    'customerEmail' => $customerEmail,
    'customerPhone' => $customerPhone,
    'preferredStart' => $preferredStart,
    'preferredEnd' => $preferredEnd,
    'requestBody' => $requestBody,
]);

// SAKURA-BLOOM 自動取り込み用ログ（独立処理・失敗してもHP予約処理に影響しない）
@file_put_contents(
    '/home/sakuranet/www/system/SAKURA-BLOOM/incoming_reservations.jsonl',
    json_encode([
        'ts'      => date('c'),
        'name'    => $customerName,
        'email'   => $customerEmail,
        'phone'   => $customerPhone,
        'company' => $company,
        'date'    => $primaryDate,
        'time'    => $slotParts[0] ?? '',
        'meeting' => $meetingType,
        'service' => $serviceType,
        'message' => $message,
        'ticket'  => $result['ticketKey'] ?? ($result['ticket']['ticketKey'] ?? ''),
    ], JSON_UNESCAPED_UNICODE) . "\n",
    FILE_APPEND | LOCK_EX
);

if (empty($result['ok'])) {
    reservation_redirect_error((string)($result['message'] ?? '予約希望を送信できませんでした。'));
}

$ticket = '';
if (isset($result['ticketKey']) && is_string($result['ticketKey'])) {
    $ticket = $result['ticketKey'];
} elseif (isset($result['ticket']['ticketKey']) && is_string($result['ticket']['ticketKey'])) {
    $ticket = $result['ticket']['ticketKey'];
}

$query = $ticket !== '' ? '?ticket=' . rawurlencode($ticket) : '';
header('Location: reservation_thanks.php' . $query, true, 303);
exit;
