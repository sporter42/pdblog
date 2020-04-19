<?php
require __DIR__ . '/../../vendor/autoload.php';

require __DIR__ . "/_pdblog.php";  
require __DIR__ . "/_pdblog_config.php";  

$pdb = new PDBlog($pdblog_dynamodb_connection, $pdblog_dynamodb_table_name, $pdblog_blog_full_base_url, $pdblog_blog_name); 

if ($pdb->check_user_session()) {
    $pdb->user_session_keep_alive();
}
?>