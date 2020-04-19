<?php
require __DIR__ . "/../_init.php";

if ($pdb->check_user_session()) {
    $pdb->user_session_keep_alive();
} else {
    header("Location: {$pdb->blog_full_base_url}/login.php");
}
?>