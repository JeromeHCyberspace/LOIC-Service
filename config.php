<?

/*
    Heroku uses an ephemeral file system... so default file-based sessions clear on every deploy
    So let's support Redis-based sessions for Heroku deploys
*/

$root_pw = $_ENV['ROOT_PW'];
$hacking_group_id = $_ENV['HACKING_GROUP_ID'];

if(isset($_ENV['REDIS_URL']) && $_ENV['REDIS_URL']) {
  $redisUrlParts = parse_url($_ENV['REDIS_URL']);
  ini_set('session.save_handler','redis');
  ini_set('session.save_path',"tcp://$redisUrlParts[host]:$redisUrlParts[port]?auth=$redisUrlParts[pass]");
}

session_start();

//Pull postgres database url from environment variables (automatically set there for Heroku)
$database_url = $_ENV['DATABASE_URL'];
if(!$database_url) {
    //default for local development/testing
    $database_url = "postgres://localhost:5432/hcservices";
}

//Build up database connection string from database_url
$databaseurl_pieces = parse_url($database_url);
$server = $databaseurl_pieces['host'];
$username = (isset($databaseurl_pieces['user']) ? $databaseurl_pieces['user'] : '');
$password = (isset($databaseurl_pieces['pass']) ? $databaseurl_pieces['pass'] : '');
$port = $databaseurl_pieces['port'];
$db = substr($databaseurl_pieces['path'], 1);

$db_connection_string = "host=".$server." dbname=".$db;
if($username) {
    $db_connection_string .= " user=".$username;
}
if($password) {
    $db_connection_string .= " password=".$password;
}
if($port) {
    $db_connection_string .= " port=".$port;
}

// Connecting, selecting database
$c = pg_connect($db_connection_string) or die('Could not connect: ' . pg_last_error());

?>
