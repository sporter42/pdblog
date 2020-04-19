<?php
$pdblog_blog_name = "my wonderful blog";
$pdblog_dynamodb_table_name = "PDBlogTable";
$pdblog_blog_full_base_url = "http://{$_SERVER['SERVER_NAME']}/pdblog";
// DynamoDB - using locally running instance of DynamoDB Local
$pdblog_dynamodb_connection = [
    'endpoint' => 'http://localhost:8000',
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => 'anything',
        'secret'  => 'it_does_not_matter'
    ]
];
/*
// 
// Examples of other DynamoDB configurations are below
//
// DynamoDB - using command-line after "aws configure" and/or IAM role attached to EC2 instance
$pdblog_dynamodb_connection = [
    'region' => 'us-east-1',
    'version' => 'latest'
];

// DynamoDB - using AWS credentials copied to files
$pdblog_dynamodb_connection = [
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => file_get_contents("/var/www/.aws/credentials.key_id"),
        'secret'  => file_get_contents("/var/www/.aws/credentials.access_key")
    ]
];
*/
?>