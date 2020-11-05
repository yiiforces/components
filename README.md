```php
<?php
include dirname(BASE_PATH) . '/components/autoload.php';

use db\Connection;
use db\Query;

$db = new Connection([
	'dsn'         => 'pgsql:host=localhost;dbname=test',
	'username'    => 'postgres',
	'password'    => 'postgres',
	'charset'     => 'utf8',
]);

$addIndex = $db->createCommand()
	->createIndex('test_index01', 'test', 'name', true)
	->execute();

var_dump($add);

$db->createCommand('insert into test (name) values (:name)', [
	':name' => 'test'
])->execute();

echo $db->createCommand()->insert('test', [
    'name' => 'Sam',
])->execute();

