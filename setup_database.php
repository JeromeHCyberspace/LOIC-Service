<?

global $c;
include("config.php");

$create_tables_sql = <<<'IZ8455SQL'
CREATE TABLE IF NOT EXISTS "loic_member" (
    "player_id" varchar(255) NOT NULL PRIMARY KEY,
    "player_name" varchar(255)
)
;
IZ8455SQL;

pg_query($c, $create_tables_sql);
?>