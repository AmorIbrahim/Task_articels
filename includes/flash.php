<?php
session_start();

function flash($type, $msg) {
    $_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg];
}

function showFlash() {
    if (!empty($_SESSION['flash'])) {
        echo '<div class="alert alert-'.$_SESSION['flash']['type'].' alert-fixed" role="alert">'
            . htmlspecialchars($_SESSION['flash']['msg']) . '</div>';
        unset($_SESSION['flash']);
        echo "<script>setTimeout(()=>{ document.querySelector('.alert-fixed')?.remove(); }, 3000);</script>";
    }
}
?>
