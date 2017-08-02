<?php include_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>AL + CT</title>
</head>
<body>
<h1>AL + CT (Adjacency List and Closure Table)</h1>
<p>Список смежности и Таблица связей</p>
<p><a href="/php/right.php" target="frame_right">Обновить правую панель</a></p>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Пересоздать</legend>
		<button type="submit" name="submitForm" value="create_schema">Пересоздать</button> <strong>базу</strong> <var><?= $config['dbName'] ?></var>.<br><br>
		<button type="submit" name="submitForm" value="create_tables">Пересоздать</button> <strong>таблицы</strong> <var><?= $config['table_tree'] ?></var>, <var><?= $config['table_tree_relation'] ?></var>.
	</fieldset>
	<fieldset>
		<legend>Вставить</legend>
		<button type="submit" name="submitForm" value="insert_test">Вставить</button> <strong>тестовые данные</strong> вместо существующих.<br><br>
		<button type="submit" name="submitForm" value="insert_million">Вставить</button> <input type="text" name="count" placeholder="количество" value="100"> <strong>записей</strong> вместо существующих.
	</fieldset>
	<fieldset>
		<legend>Добавить</legend>
		<button type="submit" name="submitForm" value="add">Добавить</button> в <input type="text" name="pid" placeholder="идентификатор" value="1"> «<input type="text" name="header" placeholder="текст элемента" value="e">».
	</fieldset>
</form>
<fieldset>
	<legend>Выбрать</legend>
	<form action="/php/right.php" method="post" target="frame_right">
		<button type="submit" name="submitForm" value="select_ancestors">Выбрать</button> <strong>предков</strong> <input type="text" name="select_ancestors_id" value="9" placeholder="идентификатор">.<br><br>
		<button type="submit" name="submitForm" value="select_descendants">Выбрать</button> <strong>потомков</strong> <input type="text" name="select_descendants_id" value="1" placeholder="идентификатор">.<br><br>
		<button type="submit" name="submitForm" value="get_childrens">Выбрать</button> <strong>детей</strong> <input type="text" name="pid" value="1" placeholder="идентификатор">.
	</form>
</fieldset>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Переместить</legend>
		<button type="submit" name="submitForm" value="move">Переместить</button> <input type="text" name="eid" value="3" placeholder="идентификатор"> в <input type="text" name="tid" value="6" placeholder="идентификатор">.
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Удалить</legend>
		<button type="submit" name="submitForm" value="delete">Удалить</button> <input type="text" name="id" value="1" placeholder="идентификатор"> <label>рекурсивно <input type="checkbox" name="recursively" value="1"></label>.
	</fieldset>
</form>
<fieldset>
	<legend>Сортировка</legend>
	<form action="/php/right.php" method="post" target="frame_right">
		<button type="submit" name="submitForm" value="order_after">Поставить</button> <input type="text" name="id" value="2" placeholder="идентификатор"> после <input type="text" name="after_id" value="3" placeholder="идентификатор">.<br><br>
	</form>
	<form action="/php/right.php" method="post" target="frame_right">
		<button type="submit" name="submitForm" value="order_first">Поставить</button> <input type="text" name="id" value="7" placeholder="идентификатор"> в начало <input type="text" name="pid" value="4" placeholder="идентификатор">.
	</form>
</fieldset>
