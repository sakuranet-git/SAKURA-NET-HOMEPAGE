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

function checkout_upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded image is too large.',
        UPLOAD_ERR_PARTIAL => 'Image upload was interrupted.',
        default => 'Image upload failed.',
    };
}

function checkout_uploaded_product_file(int|string $index): ?array
{
    if (!isset($_FILES['products']) || !is_array($_FILES['products'])) {
        return null;
    }

    $file = $_FILES['products'];
    $error = $file['error'][$index]['image_file'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return [
        'name' => (string) ($file['name'][$index]['image_file'] ?? ''),
        'type' => (string) ($file['type'][$index]['image_file'] ?? ''),
        'tmp_name' => (string) ($file['tmp_name'][$index]['image_file'] ?? ''),
        'error' => (int) $error,
        'size' => (int) ($file['size'][$index]['image_file'] ?? 0),
    ];
}

function checkout_save_uploaded_image(int|string $index, string $productId): ?string
{
    $upload = checkout_uploaded_product_file($index);
    if ($upload === null) {
        return null;
    }

    if ($upload['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException(checkout_upload_error_message((int) $upload['error']));
    }

    if ($upload['size'] <= 0 || $upload['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Image size must be 5MB or smaller: ' . $productId);
    }

    $extension = strtolower(pathinfo((string) $upload['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new InvalidArgumentException('Image extension must be jpg, jpeg, png, or webp: ' . $productId);
    }

    $tmpName = (string) $upload['tmp_name'];
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new InvalidArgumentException('Uploaded image could not be verified: ' . $productId);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpName);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedMimes[$mime])) {
        throw new InvalidArgumentException('Image MIME type is not allowed: ' . $productId);
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Upload directory could not be created.');
    }

    $safeId = preg_replace('/[^A-Za-z0-9_-]/', '-', $productId);
    $filename = $safeId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Uploaded image could not be saved: ' . $productId);
    }

    @chmod($destination, 0644);

    return 'uploads/' . $filename;
}

try {
    $submitted = $_POST['products'] ?? [];
    if (!is_array($submitted)) {
        throw new InvalidArgumentException('Invalid product payload.');
    }

    $existingProducts = checkout_products();
    $existingById = [];
    foreach ($existingProducts as $existingProduct) {
        if (is_array($existingProduct) && isset($existingProduct['id'])) {
            $existingById[(string) $existingProduct['id']] = $existingProduct;
        }
    }

    $products = [];
    $seen = [];

    foreach ($submitted as $index => $row) {
        if (!is_array($row) || !empty($row['delete'])) {
            continue;
        }

        $id = trim((string) ($row['id'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $description = trim((string) ($row['description'] ?? ''));
        $amountRaw = trim((string) ($row['amount'] ?? ''));
        $category = trim((string) ($row['category'] ?? ''));
        $imageInput = trim((string) ($row['image'] ?? ''));
        $hasUpload = checkout_uploaded_product_file($index) !== null;

        if ($id === '' && $name === '' && $description === '' && $amountRaw === '' && $category === '' && $imageInput === '' && !$hasUpload) {
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

        $existing = $existingById[$id] ?? [];
        $image = trim((string) ($existing['image'] ?? ''));
        if ($imageInput !== '') {
            $image = $imageInput;
        }

        $uploadedImage = checkout_save_uploaded_image($index, $id);
        if ($uploadedImage !== null) {
            $image = $uploadedImage;
        }

        if ($category === '') {
            $category = trim((string) ($existing['category'] ?? 'Products'));
        }

        $seen[$id] = true;
        $products[] = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'amount' => (int) $amount,
            'category' => $category !== '' ? $category : 'Products',
            'image' => $image,
        ];
    }

    checkout_save_products($products);
    $_SESSION['checkout_message'] = '商品を保存しました。';
} catch (Throwable $e) {
    $_SESSION['checkout_error'] = $e->getMessage();
}

header('Location: products.php');
exit;
