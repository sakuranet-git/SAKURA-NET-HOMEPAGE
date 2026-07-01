<?php
declare(strict_types=1);

session_start();
require_once dirname(__DIR__) . '/config.php';

if (empty($_SESSION['checkout_admin'])) {
    header('Location: login.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: products.php');
    exit;
}

$token = (string) ($_POST['csrf'] ?? '');
$sessionToken = (string) ($_SESSION['checkout_csrf'] ?? '');
if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    $_SESSION['checkout_error'] = 'CSRF token error.';
    header('Location: products.php');
    exit;
}

try {
    $submitted = $_POST['products'] ?? [];
    if (!is_array($submitted)) {
        throw new InvalidArgumentException('Invalid product payload.');
    }

    $products = [];
    $seen = [];

    foreach ($submitted as $row) {
        if (!is_array($row) || !empty($row['delete'])) {
            continue;
        }

        $id = trim((string) ($row['id'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $description = trim((string) ($row['description'] ?? ''));
        $amountRaw = trim((string) ($row['amount'] ?? ''));

        if ($id === '' && $name === '' && $description === '' && $amountRaw === '') {
            continue;
        }

        if ($id === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $id)) {
            throw new InvalidArgumentException('ID must use only letters, numbers, hyphen, or underscore.');
        }

        if (isset($seen[$id])) {
            throw new InvalidArgumentException('Duplicate product ID: ' . $id);
        }

        if ($name === '') {
            throw new InvalidArgumentException('Product name is required: ' . $id);
        }

        $amount = filter_var($amountRaw, FILTER_VALIDATE_INT);
        if ($amount === false || $amount <= 0) {
            throw new InvalidArgumentException('Amount must be a positive integer: ' . $id);
        }

        $seen[$id] = true;
        $products[] = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'amount' => (int) $amount,
        ];
    }

    checkout_save_products($products);
    $_SESSION['checkout_message'] = '商品を保存しました。';
} catch (Throwable $e) {
    $_SESSION['checkout_error'] = $e->getMessage();
}

header('Location: products.php');
exit;
