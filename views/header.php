<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="utf-8">
  <title>لوحة التحكم</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body dir="rtl">

<button class="btn btn-dark toggle-btn" onclick="toggleSidebar()">☰</button>
<?php include "includes/sidebar.php"; ?>
<div class="content">
<?php showFlash(); ?>
<div class="tab-content">
