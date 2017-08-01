<?php

use Zlatov\Tree\Tree;

$opt = [
    // PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
    // PDO::ATTR_ERRMODE            => PDO::ERRMODE_WARNING,
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // За обработку подготовленных выражений отвечает сам PDO.
    PDO::ATTR_STRINGIFY_FETCHES => false, // Преобразовывать числовые значения в строки во время выборки.
];

$link = sprintf('mysql:host=%1$s;charset=%2$s', $config['host'], $config['charset']);
$pdo = new PDO($link, $config['user'], $config['password'], $opt);


function check_schema_exist()
{
    global $pdo, $config;
    $sql = sprintf(
        "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s';",
        $config['dbName']
    );
    $stmt = $pdo->query($sql);
    $db = $stmt->fetchColumn();
    if ($db) {
        return true;
    } else {
        return false;
    }
}

function schema_select()
{
    global $pdo, $config;
    $sql = sprintf("USE %s;", $config['dbName']);
    $pdo->exec($sql);
}

function check_schema_select()
{
    global $pdo, $config;
    $db = $pdo->query('SELECT database();')->fetchColumn();

    if (!$db) {
        echo "<p>База данных не выбрана.</p>";
        return false;
    } else {
        echo "<p>Выбрана база данных: $db</p>";
        return true;
    }
}

function check_tables_exist()
{
    global $pdo, $config;
    $sql = sprintf("SHOW TABLES LIKE '%s';", $config['table_tree']);
    $dbTable1 = $pdo->query($sql)->fetchColumn();
    $sql = sprintf("SHOW TABLES LIKE '%s';", $config['table_tree_relation']);
    $dbTable2 = $pdo->query($sql)->fetchColumn();
    if (!$dbTable1||!$dbTable2) {
        echo "<p>Нет таблиц.</p>";
        return false;
    }
    return true;
}

function get_items()
{
    global $pdo, $config;
    $sql = sprintf("SELECT * FROM `%s` ORDER BY `order` ASC, `id` ASC;", $config['table_tree']);
    $stmt = $pdo->query($sql);
    $tree = $stmt->fetchAll();
    return $tree;
}

function get_count()
{
    global $pdo, $config;
    $sql = sprintf("SELECT count(id) FROM `%s`;", $config['table_tree']);
    $stmt = $pdo->query($sql);
    $count = $stmt->fetchColumn(0);
    return $count;
}

function create_schema()
{
    global $pdo, $config;
    $sql = sprintf(
        file_get_contents(__DIR__ . '/../sql/create_schema.sql'),
        $config['dbName']
    );
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "<p>{$e->errorInfo[2]}</p>";
        die();
    }
    echo "<p>База данных {$config['dbName']} создана.</p>";
    schema_select();
}

function create_tables()
{
    global $pdo, $config;
    try {
        $sql = sprintf(
            file_get_contents(__DIR__ . '/../sql/create_tables.sql'),
            $config['table_tree'],
            $config['table_tree_relation']
        );
        $pdo->exec($sql);
        $sql = sprintf(
            file_get_contents(__DIR__ . '/../sql/triggers.sql'),
            $config['table_tree'],
            $config['table_tree_relation']
        );
        $pdo->exec($sql);
        $sql = sprintf(
            file_get_contents(__DIR__ . '/../sql/procedures.sql'),
            $config['table_tree'],
            $config['table_tree_relation']
        );
        $pdo->exec($sql);
        echo "<p>Таблицы, процедуры и триггреы созданы.</p>";
    } catch (PDOException $e) {
        echo "<p>{$e->errorInfo[2]}</p>";
        die();
    }
}

function insert_test()
{
    global $pdo, $config;
    try {
        $sql = sprintf(
            file_get_contents(__DIR__ . '/../sql/insert_test.sql'),
            $config['table_tree']
        );
        $pdo->exec($sql);
        echo "<p>Тестовые данные вставлены.</p>";
    } catch (PDOException $e) {
        die("<p>{$e->errorInfo[2]}</p>");
    }
}

function insert_million()
{
    global $pdo, $config;

    try {
        $sql_length = 1000;
        $sql = '';
        $count = intval($_POST['count']);
        if (!$count)
        {
            return null;
        }
        $levels = ceil(log($count));
        $level = 1;
        $count_level = ceil($count/$levels);
        $count_on_level = $count_level*$level;
        for ($i=1; $i <= $count; $i++) {
            if (($i-1)%$sql_length===0) {
                $sql.= 'INSERT INTO `%s` (`id`, `pid`, `header`) VALUES ';
            }
            if ($i>$count_on_level) {
                $level++;
                $count_on_level = $count_level*$level;
            }
            if ($level===1) {
                $from = 0;
                $to = 0;
                $pid = 'NULL';
            } else {
                $from = $count_level*($level-1)-$count_level+1;
                $to = $count_level*($level-1);
                $pid = mt_rand($from, $to);
            }
            $sql.= ($i===$count||$i%$sql_length===0)?"($i, $pid, 'e$i');":"($i, $pid, 'e$i'),";
            if ($i===$count||$i%$sql_length===0) {
                $sql = sprintf(
                    $sql,
                    $config['table_tree']
                );
                // echo $sql.'<br>';
                $pdo->exec($sql);
                $sql = '';
            }
        }

        echo "<p>Тестовые данные вставлены.</p>";
    } catch (PDOException $e) {
        die("<p>{$e->errorInfo[2]}</p>");
    }
}

function add()
{
    global $pdo, $config;
    if (empty($_POST['header'])) {
        echo "<p>Не задан header.</p>";
        return;
    }
    try {
        $pdo->exec("START TRANSACTION;");
        $sql = sprintf(
            'INSERT INTO `%s` (`pid`, `header`) VALUES (:pid, :header);',
            $config['table_tree']
        );
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':header', $_POST['header'], PDO::PARAM_STR);
        $stmt->bindValue(':pid', (!empty($_POST['pid']))?$_POST['pid']:NULL, PDO::PARAM_INT);
        $stmt->execute();
        $pdo->exec("COMMIT;");
    } catch (PDOException $e) {
        $pdo->exec("ROLLBACK;");
        die("<p>{$e->errorInfo[2]}</p>");
    }
    echo "<p>Элемент добавлен.</p>";
}

function delete()
{
    global $pdo, $config;
    if (empty($_POST['id'])) {
        echo "<p>Не задан id.</p>";
        return;
    }
    try {
        $pdo->exec("START TRANSACTION;");
        $sql = sprintf(
            '
                DELETE FROM `%s`
                WHERE `id` = :id;
            ',
            $config['table_tree']
        );
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
        $pdo->exec("COMMIT;");
    } catch (PDOException $e) {
        $pdo->exec("ROLLBACK;");
        die("<p>{$e->errorInfo[2]}</p>");
    }
    echo "<p>Что-то удалено.</p>";
}

function select_descendants()
{
    global $pdo, $config;
    if (empty($_POST['select_descendants_id'])) {
        echo "<p>Не задан id.</p>";
        return;
    }
    $sql = sprintf(
        '
            SELECT t.*
            FROM %2$s r
            LEFT JOIN %1$s t ON t.id = r.did
            WHERE r.aid = :aid
        ',
        $config['table_tree'],
        $config['table_tree_relation']
    );
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':aid', $_POST['select_descendants_id'], PDO::PARAM_INT);
    $stmt->execute();
    $flat = $stmt->fetchAll();
    $nested = Tree::to_nested($flat, ['root_id' => $_POST['select_descendants_id']]);
    $html = Tree::to_html($nested, ['tpl_li' => '<li>??header?? <small>#??id??</small> <small>^??level??</small>']);
    echo "<p>Потомки:</p>";
    echo $html;
}

function select_ancestors()
{
    global $pdo, $config;
    if (empty($_POST['select_ancestors_id'])) {
        echo "<p>Не задан id.</p>";
        return;
    }
    $sql = sprintf(
        '
            SELECT t.*
            FROM %2$s r
            JOIN %1$s t ON t.id = r.aid
            WHERE r.did = :did;
        ',
        $config['table_tree'],
        $config['table_tree_relation']
    );
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':did', $_POST['select_ancestors_id'], PDO::PARAM_INT);
    $stmt->execute();
    $flat = $stmt->fetchAll();
    $nested = Tree::to_nested($flat);
    $html = Tree::to_html($nested, ['tpl_li' => '<li>??header?? <small>#??id??</small> <small>^??level??</small>']);
    echo "<p>Предки:</p>";
    echo $html;
}


function move()
{
    global $pdo, $config;
    if (empty($_POST['eid'])) {
        echo "<p>Не задан id перемещаемого элемента.</p>";
        return;
    }
    try {
        $pdo->exec("START TRANSACTION;");
        $sql = sprintf(
            '
                UPDATE `%s`
                SET `pid` = :tid
                WHERE `id` = :eid;
            ',
            $config['table_tree']
        );
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':eid', $_POST['eid'], PDO::PARAM_INT);
        $stmt->bindValue(':tid', (!empty($_POST['tid']))?$_POST['tid']:NULL, PDO::PARAM_INT);
        $stmt->execute();
        echo "<p>Элемент перемещён.</p>";
        $sql = sprintf('call %1$s_update_level_moved_descendants(:id)', $config['table_tree']);
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $_POST['eid'], PDO::PARAM_INT);
        $stmt->execute();
        $pdo->exec("COMMIT;");
        echo "<p>Уровни потомков изменены.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ROLLBACK;");
        die("<p>{$e->errorInfo[2]}</p>");
    }
}

function order_after()
{
    global $pdo, $config;
    if (empty($_POST['id'])||empty($_POST['after_id'])) {
        echo "<p>Не задан id.</p>";
        return;
    }
    try {
        $pdo->exec("START TRANSACTION;");
        $sql = sprintf('CALL %1$s_order_after(:id, :after_id)', $config['table_tree']);
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->bindValue(':after_id', $_POST['after_id'], PDO::PARAM_INT);
        $stmt->execute();
        $pdo->exec("COMMIT;");
        echo "<p>Элемент поставлен.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ROLLBACK;");
        die("<p>{$e->errorInfo[2]}</p>");
    }
}

function order_first()
{
    global $pdo, $config;
    if (empty($_POST['id'])) {
        echo "<p>Не задан id.</p>";
        return;
    }
    try {
        $pdo->exec("START TRANSACTION;");
        $sql = sprintf('CALL %1$s_order_first(:id, :pid)', $config['table_tree']);
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->bindValue(':pid', empty($_POST['pid'])?NULL:$_POST['pid'], PDO::PARAM_INT);
        $stmt->execute();
        $pdo->exec("COMMIT;");
        echo "<p>Элемент поставлен.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ROLLBACK;");
        die("<p>{$e->errorInfo[2]}</p>");
    }
}
