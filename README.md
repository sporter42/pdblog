# PDBlog
PDBlog is a minimalist blog implemented using PHP and DynamoDB.

## Requirements

* DynamoDB, by way of one of:
  * [DynamoDB Local](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/DynamoDBLocal.DownloadingAndRunning.html)
  * an EC2 instance with access to DynamoDB (preferably using an IAM role)
  * a Lightsail instance and an IAM User with access to DynamoDB
* PHP 7.1 or later, plus the following libraries
  * [PHP Markdown](https://github.com/michelf/php-markdown)
  * [AWS SDK for PHP](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html)
* Linux and Apache 2.4
  * mod_rewrite enabled and configurable via .htaccess

## Installation
```bash
$ git clone git@github.com:sporter42/pdblog.git
```

### Basic Configuration

* move "www" to a web-accessible directory
* edit www/.htaccess to set the correct base URL for rewriting
* edit www/_init.php to reflect the correct path to your autoload files
* edit www/_pdblog_config.php
  * set $pdblog_blog_full_base_url to the URL that points to the www files
  * set $pdblog_dynamodb_connection to connect to your DynamoDB installation; the default configuration will connect to DynamoDB Local running on localhost port 8000
* move "scripts" to a non-web-accesible directory
* edit scripts/add_user.php
  * to reflect the correct path to your autoload files
  * to reflect the correct path to the web-accessible files

After doing this configuration, you should be able to the URL for PDBlog in your browser and see the message "There are no posts."

To add/edit/delete posts, you'll need a login. Execute the scripts/add_user.php script, supplying the following parameters:

* username
* password
* email address
* full name

```bash
$ php add_user.php "testuser" "mypassword" "myemail@domain.com" "John Doe"
```

Now you should be able to login. Go to the URL for PDBlog followed by "/admin", which should display a login form. Enter the username/password you just supplied and you will be logged in.

### Additional Configuration

See www/_pdbog_config.php.