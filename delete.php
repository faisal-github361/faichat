<?php
include('db.php');
header('Content-Type: text/plain; charset=utf-8');

$id  = (int)($_POST['id'] ?? 0);
$uid = $_COOKIE['chat_uid'] ?? '';

if ($id <= 0) {
    echo 'error: bad id';
    exit;
}
if ($uid === '') {
    echo 'error: no chat_uid cookie, reload the page and try again';
    exit;
}

try {
    // يحذف الرسالة بس إذا كانت لك
    $stmt = mysqli_prepare($con, "DELETE FROM chat WHERE id = ? AND owner = ?");
    mysqli_stmt_bind_param($stmt, 'is', $id, $uid);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo 'ok';
    } else {
        echo 'error: message not found or it is not yours';
    }
} catch (Throwable $e) {
    echo 'error: ' . $e->getMessage();
}