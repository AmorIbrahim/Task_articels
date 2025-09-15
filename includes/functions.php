<?php
session_start();

function flash($type, $msg) {
    $_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg];
}

function showFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-'.$f['type'].' alert-fixed">'.htmlspecialchars($f['msg']).'</div>';
        unset($_SESSION['flash']);
        echo "<script>setTimeout(()=>{ document.querySelector('.alert-fixed')?.remove(); }, 3000);</script>";
    }
}

function getSort($prefix, $default_col, $default_dir = "ASC") {
    $col = $_GET[$prefix . "_sort"] ?? $default_col;
    $dir = strtoupper($_GET[$prefix . "_dir"] ?? $default_dir);
    return [$col, ($dir === "DESC") ? "DESC" : "ASC"];
}

function sortLink($prefix, $col, $title, $current_col, $current_dir) {
    $dir = ($current_col == $col && $current_dir == "ASC") ? "DESC" : "ASC";
    $query = $_GET;
    $query[$prefix . "_sort"] = $col;
    $query[$prefix . "_dir"]  = $dir;
    $url = "?" . http_build_query($query);
    $arrow = ($current_col == $col) ? ($current_dir == "ASC" ? "▲" : "▼") : "";
    return "<a href='$url'>$title $arrow</a>";
}

function renderPagination($prefix, $page, $total_pages) {
    if ($total_pages <= 1) return;
    echo '<nav><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? "active" : "";
        $query = $_GET;
        $query[$prefix] = $i;
        $url = "?" . http_build_query($query);
        echo "<li class='page-item $active'><a class='page-link' href='$url'>$i</a></li>";
    }
    echo '</ul></nav>';
}
