<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/flash.php";

if (isset($_POST['add_user'])) {
    $name   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $role   = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,status,created_at,updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
    $default_pass = '123456';
    $stmt->bind_param("sssss", $name, $email, $default_pass, $role, $status);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم إضافة المستخدم بنجاح.');
    header("Location: index.php#users"); exit;
}

if (isset($_POST['edit_user'])) {
    $id     = intval($_POST['id']);
    $name   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $role   = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم تحديث بيانات المستخدم.');
    header("Location: index.php#users"); exit;
}

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم حذف المستخدم.');
    header("Location: index.php#users"); exit;
}
?>
