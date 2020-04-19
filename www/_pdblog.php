<?php
date_default_timezone_set('UTC');

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Michelf\MarkdownExtra;

class PDBlog 
{
    /*
     * Constructor
     */
    function __construct($dynamodb_connection, $dynamodb_table_name, $blog_full_base_url, $blog_name) {
        $this->dynamodb_connection = $dynamodb_connection;
        $this->dynamodb_table_name = $dynamodb_table_name;
        $this->blog_full_base_url = $blog_full_base_url;
        $this->blog_name = $blog_name;

        $this->blog_id = substr(hash('sha1', "{$blog_name}{$dynamodb_table_name}"), 0, 10);
        $this->session_id_cookiename = "{$this->blog_id}_sid";
        $this->session_token_cookiename = "{$this->blog_id}_t";
        $this->authenticated = FALSE;
        
        $this->tz = 'America/New_York';
    }

    /*
     * Get a connection to DynamoDB
     */
    private function get_connection() {
        $sdk = new Aws\Sdk($this->dynamodb_connection);
        return $sdk->createDynamoDb();
    }

    /*
     * Initialize DynamoDB database
     */
    function dynamodb_initialize()
    {
        $params = [
            'TableName' => $this->dynamodb_table_name,
            'BillingMode' => 'PAY_PER_REQUEST',
            'KeySchema' => [
                [
                    'AttributeName' => 'PK',
                    'KeyType' => 'HASH'  //Partition key
                ],
                [
                    'AttributeName' => 'SK',
                    'KeyType' => 'RANGE'  //Sort key
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'PK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'SK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'RelatesTo',
                    'AttributeType' => 'S'
                ]
            ],
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => 'GSI1',
                    'Projection' => [
                        'ProjectionType' => 'ALL'
                    ],
                    'KeySchema' => [
                        [
                            'AttributeName' => 'RelatesTo',
                            'KeyType' => 'HASH'  //Partition key
                        ],
                        [
                            'AttributeName' => 'PK',
                            'KeyType' => 'RANGE'  //Sort key
                        ]
                    ]
                ]
            ]
        ];

        $dynamodb = $this->get_connection();
        
        try {
            $result = $dynamodb->createTable($params);
        } catch (DynamoDbException $e) {
            echo "Unable to create table:\n";
            echo $e->getMessage() . "\n";
        }    
    }    

    /*
     * List all posts
     */
    public function list_blog_posts() {
        $marshaler = new Marshaler();

        $blog_post = [
            ':blogPost' => "BlogPost"
        ];
        $blog_post_av = $marshaler->marshalItem($blog_post);

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'IndexName' => 'GSI1',
            'KeyConditionExpression' => 'RelatesTo = :blogPost',
            'ExpressionAttributeValues' => $blog_post_av,
            'ScanIndexForward' => false
        ];

        $dynamodb = $this->get_connection();

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            if ($e->getAwsErrorCode() == 'ResourceNotFoundException') {
                $this->dynamodb_initialize();
                sleep(2);
            } else {
                echo "Failure:\n";
                echo $e->getMessage() . "\n";
            }
        }

        return $this->blog_posts_from_items($result['Items']);
    }

    /*
     * Get a single BlogPost
     */
    public function get_blog_post($y, $m, $d, $slug) {
        $marshaler = new Marshaler();

        $pk = [
            ':PK' => "BlogPost#{$y}#{$m}#{$d}#{$slug}"
        ];
        $pk_av = $marshaler->marshalItem($pk);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'KeyConditionExpression' => 'PK = :PK',
            'ExpressionAttributeValues' => $pk_av
        ];

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            echo "Failure:\n";
            echo $e->getMessage() . "\n";
        }
        
        return $this->blog_posts_from_items($result['Items'])[0];
    }
    
    /*
     * Translate a set of Items (response from API) to an array of blogPosts
     */
    function blog_posts_from_items($items) {
        if (!is_array($items) || sizeof($items) == 0) { return array(); }
    
        $marshaler = new Marshaler();
        
        foreach ($items as $blog_post_av) {
            $blog_post = $marshaler->unmarshalItem($blog_post_av);

            $pk_parts = explode('#', $blog_post['PK']);
            $blog_post['Y'] = $pk_parts[1];
            $blog_post['M'] = $pk_parts[2];
            $blog_post['D'] = $pk_parts[3];
            $blog_post['Slug'] = $pk_parts[4];
        
            $blog_post['RelativeURL'] = "{$blog_post['Y']}/{$blog_post['M']}/{$blog_post['D']}/{$blog_post['Slug']}";
            $blog_post['Date'] = new DateTime("{$blog_post['Y']}-{$blog_post['M']}-{$blog_post['D']}");
            $blog_post['IsExternalLink'] = (isset($blog_post['LinkURL'])) ? TRUE : FALSE; 
            $blog_post['CreatedAtDT'] = new DateTime($blog_post['CreatedAt']);
            $blog_post['UpdatedAtDT'] = $blog_post['CreatedAtDT'];
            $blog_post['ContentHtml'] = MarkdownExtra::defaultTransform($blog_post['Content']);

            $blog_posts_ca[$blog_post['CreatedAt']] = $blog_post;
        }
        
        $created_ats = array_keys($blog_posts_ca);
        arsort($created_ats);
        foreach ($created_ats as $created_at) {
        	$blog_posts[] = $blog_posts_ca[$created_at];
        }
        
        return $blog_posts;
    }

    /*
     * Add/revise a blog post
     */
    function add_blog_post($y, $m, $d, $slug, $title, $link_url, $content, $created_by, $created_by_full_name) {
        $marshaler = new Marshaler();
        
        $blog_post_pk = "BlogPost#{$y}#{$m}#{$d}#{$slug}";
        
        $blog_post = [
            'PK' => $blog_post_pk,
            'SK' => 'BlogPost',
            'RelatesTo' => 'BlogPost',
            'Title' => $title,
            'Slug' => $slug,
            'Content' => $content,
            'CreatedBy' => $created_by,
            'CreatedByFullName' => $created_by_full_name,
            'CreatedAt' => gmdate(DATE_ISO8601)
        ];
        
        if (!empty($link_url)) {
            $blog_post['LinkURL'] = $link_url;
        }
        
        $blog_post_av = $marshaler->marshalItem($blog_post);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Item' => $blog_post_av
        ];

        try {
            $result = $dynamodb->putItem($params);
        } catch (DynamoDbException $e) {
            echo "Unable to add item:\n";
            echo $e->getMessage() . "\n";
        }
    }

    /*
     * Delete a BlogPost
     */
    public function delete_blog_post($y, $m, $d, $slug) {
        $dynamodb = $this->get_connection();
        $marshaler = new Marshaler();

        $pk = [
            'PK' => "BlogPost#{$y}#{$m}#{$d}#{$slug}",
            'SK' => "BlogPost"
        ];
        $pk_av = $marshaler->marshalItem($pk);

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Key' => $pk_av
        ];

        try {
            $result = $dynamodb->deleteItem($params);
        } catch (DynamoDbException $e) {
            echo "Failure:\n";
            echo $e->getMessage() . "\n";
        }
        
        return TRUE;
    }
    
    /*
     * Add/edit a user
     */
    function add_user($username, $password, $email_address, $full_name) {
        $marshaler = new Marshaler();
        
        $salt = $this->generate_random_string(20);
        $password_hashed = hash('sha256', "{$password}{$salt}");
        
        $user = [
            'PK' => "User#{$username}",
            'SK' => 'User',
            'RelatesTo' => 'User',
            'Name' => $full_name,
            'Email' => $email_address,
            'Salt' => $salt,
            'PasswordHashed' => $password_hashed,
            'CreatedAt' => gmdate(DATE_ISO8601)
        ];
        $user_av = $marshaler->marshalItem($user);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Item' => $user_av
        ];
        
        try {
            $result = $dynamodb->putItem($params);
            echo 'Item Added.';

        } catch (DynamoDbException $e) {
            echo "Unable to add item:\n";
            echo $e->getMessage() . "\n";
        }        

        // TODO: revise full name on prior posts
    }
    
    /*
     * Retrieve user
     */
    function get_user($username) {
        $marshaler = new Marshaler();

        $pk = [
            ':PK' => "User#{$username}"
        ];
        $pk_av = $marshaler->marshalItem($pk);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'KeyConditionExpression' => 'PK = :PK',
            'ExpressionAttributeValues' => $pk_av
        ];

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            echo "Failure:\n";
            echo $e->getMessage() . "\n";
        }
        
        if (sizeof($result['Items']) == 1) {
            $user = $marshaler->unmarshalItem($result['Items'][0]);
            $user['Username'] = $username;
            return $user;
        } else {
            return NULL;
        }
    }

    /*
     * Login attempt
     */
    function login_attempt($username, $password) {
        $user = $this->get_user($username);

        if ($user['PasswordHashed'] == hash('sha256', "{$password}{$user['Salt']}")) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /*
     * Initiate a user session
     */
    function create_user_session($username) {
        $marshaler = new Marshaler();
        
        $session_id = $this->generate_random_string(20);
        $session_token = $this->generate_random_string(200);
        
        $user_session = [
            'PK' => "UserSession#{$session_id}",
            'SK' => 'UserSession',
            'RelatesTo' => 'UserSession',
            'SessionStatus' => 'Active',
            'Username' => $username,
            'Token' => $session_token,
            'InitiatedAt' => gmdate(DATE_ISO8601),
            'ExpiresAt' => gmdate(DATE_ISO8601, strtotime("+1 hour"))
        ];
        $user_session_av = $marshaler->marshalItem($user_session);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Item' => $user_session_av
        ];
        
        try {
            $result = $dynamodb->putItem($params);
            setcookie($this->session_id_cookiename, $session_id);
            setcookie($this->session_token_cookiename, $session_token);
        } catch (DynamoDbException $e) {
            echo "Unable to add item:\n";
            echo $e->getMessage() . "\n";
        }        
    }
    
    /*
     * Check a user session
     */
    function check_user_session() {
        $session_id = $_COOKIE[$this->session_id_cookiename];
        $session_token = $_COOKIE[$this->session_token_cookiename];
    
        $marshaler = new Marshaler();

        $pk = [
            ':PK' => "UserSession#{$session_id}"
        ];
        $pk_av = $marshaler->marshalItem($pk);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'KeyConditionExpression' => 'PK = :PK',
            'ExpressionAttributeValues' => $pk_av
        ];

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            if ($e->getAwsErrorCode() == 'ResourceNotFoundException') {
                $this->dynamodb_initialize();
                sleep(2);
            } else {
                echo "Failure:\n";
                echo $e->getMessage() . "\n";
            }
        }
        
        if (sizeof($result['Items']) != 1) {
            return FALSE;
        }
        
        $user_session = $marshaler->unmarshalItem($result['Items'][0]);
        
        if ($user_session['Token'] == $session_token && isset($user_session['ExpiresAt']) && new DateTime($user_session['ExpiresAt']) > new DateTime()) {
            $this->authenticated = TRUE;
            $this->authenticated_as = $this->get_user($user_session['Username']);
            $this->user_session = $user_session;
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /*
     * End a user session
     */
    function end_user_session() {
        $session_id = $_COOKIE[$this->session_id_cookiename];
        $session_token = $_COOKIE[$this->session_token_cookiename];
    
        $marshaler = new Marshaler();

        $key = [
            'PK' => "UserSession#{$session_id}",
            'SK' => "UserSession"
        ];
        $key_av = $marshaler->marshalItem($key);

        $dynamodb = $this->get_connection();

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Key' => $key_av
        ];

        try {
            $result = $dynamodb->deleteItem($params);
        } catch (DynamoDbException $e) {
            echo "Failure:\n";
            echo $e->getMessage() . "\n";
            return FALSE;
        }

        return TRUE;
    }    


    /*
     * Continue user session
     */
    function user_session_keep_alive() {
        $session_id = $_COOKIE[$this->session_id_cookiename];
        $session_token = $_COOKIE[$this->session_token_cookiename];
        $dynamodb = $this->get_connection();
        $marshaler = new Marshaler();

        $key = [
            'PK' => "UserSession#{$session_id}",
            'SK' => "UserSession"
        ];
        $key_av = $marshaler->marshalItem($key);

        $e = [
            ':ea' => gmdate(DATE_ISO8601, strtotime("+1 hour"))
        ];
        $e_av = $marshaler->marshalItem($e);

        $params = [
            'TableName' => $this->dynamodb_table_name,
            'Key' => $key_av,
            'UpdateExpression' => 'SET ExpiresAt = :ea',
            'ExpressionAttributeValues' => $e_av
        ];

        try {
            $result = $dynamodb->updateItem($params);
        } catch (DynamoDbException $e) {
            echo "Failure:\n";
            echo $e->getMessage() . "\n";
            return FALSE;
        }

        return TRUE;
    }    
    /*
     * Helper functinos
     */
    function generate_random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_len = strlen($characters);
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $characters_len - 1)];
        }
        return $random_string;
    }
}
?>
