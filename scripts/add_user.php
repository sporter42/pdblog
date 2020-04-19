<?php
require "../../vendor/autoload.php";

require "../www/_pdblog_config.php";  
require "../www/_pdblog.php";

$pdb = new PDBlog($pdblog_dynamodb_connection, $pdblog_dynamodb_table_name, $pdblog_blog_full_base_url, $pdblog_blog_name); 

$username = $argv[1];
$password = $argv[2];
$email_address = $argv[3];
$full_name = $argv[4];

$pdb->add_user($username, $password, $email_address, $full_name);
?>
