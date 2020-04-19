<?php 
require "_init.php";

$blog_post = $pdb->delete_blog_post(
    $_GET['Y'] ?? $_POST['Y'], 
    $_GET['M'] ?? $_POST['M'], 
    $_GET['D'] ?? $_POST['D'], 
    $_GET['Slug'] ?? $_POST['Slug']);

header("Location: {$pdb->blog_full_base_url}/");
?>
