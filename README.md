only psql support!
example of usage
```php
<?php
// define path structs
define('BASE_PATH'    , sys_get_temp_dir() .'/'. md5(__DIR__) ); // unique  temporal path
define('RUNTIME_PATH' , BASE_PATH   . '/runtime');
define('CACHE_PATH'   , BASE_PATH   . '/runtime/cache');
define('LOG_PATH'     , BASE_PATH   . '/runtime/logs');
define('ALIASES_FILE' , BASE_PATH   . '/runtime/aliases.json');

if(!is_dir(RUNTIME_PATH))
{
	@mkdir(RUNTIME_PATH, 0777, true);
	file_put_contents(RUNTIME_PATH . '/.gitignore', '*' . PHP_EOL . '!.gitignore');
}

if(!is_dir(LOG_PATH))
	@mkdir(LOG_PATH, 0777, true);

if(!is_dir(CACHE_PATH))
	@mkdir(CACHE_PATH, 0777, true);

if(!is_dir(PARAMS_PATH))
	@mkdir(PARAMS_PATH, 0777, true);

if(!is_dir(DATA_PATH))
{
	@mkdir(DATA_PATH, 0777, true);
	@touch(DATA_PATH . '/.gitkeep');
}

if(!file_exists(ALIASES_FILE))
	file_put_contents(ALIASES_FILE, '{}');

// include autoload
include dirname(__FILE__) . '/autoload.php';

// implementation:
use db\Connection;
use db\ColumnSchemaBuilder;
use db\pgsql\Schema;

// create new connection
$db = new Connection([
	'dsn'         => 'pgsql:host=localhost;dbname=test',
	'username'    => 'postgres',
	'password'    => 'postgres',
	'charset'     => 'utf8',
]);

// create table test
$db->createCommand()->createTable('test', [
	'id'   => new ColumnSchemaBuilder(Schema::TYPE_PK),
	'name' => new ColumnSchemaBuilder(Schema::TYPE_STRING, 255),
])->execute();

// add index
$db->createCommand()
	->createIndex('test_index01', 'test', 'name', true)
	->execute();

// any query
$db->createCommand('insert into test (name) values (:name)', [
	':name' => 'test'
])->execute();

$db->createCommand()->insert('test', [
	'name' => 'Sam',
])->execute();

// get list
var_dump($db->createCommand('select * from test')->queryAll());
