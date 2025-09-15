<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/flash.php";

if (isset($_POST['add_article'])) {
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $user_id = intval($_POST['user_id']);
    $status  = $_POST['status'] ?? 'draft';

    $stmt = $conn->prepare("INSERT INTO articles (user_id,title,content,status,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())");
    $stmt->bind_param("isss", $user_id, $title, $content, $status);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم إضافة المقال.');
    header("Location: index.php#articles"); exit;
}

if (isset($_POST['edit_article'])) {
    $id      = intval($_POST['id']);
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status  = $_POST['status'] ?? 'draft';

    $stmt = $conn->prepare("UPDATE articles SET title=?, content=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $status, $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم تحديث المقال.');
    header("Location: index.php#articles"); exit;
}

if (isset($_POST['delete_article'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم حذف المقال.');
    header("Location: index.php#articles"); exit;
}
?>
    