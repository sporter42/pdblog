<?php 
require "_init.php";

$pdb->end_user_session();
header("Location: {$pdb->blog_full_base_url}/");
?>
