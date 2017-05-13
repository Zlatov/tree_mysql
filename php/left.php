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
		<legend>Пересоздать базу <?= $config['dbName'] ?></legend>
		<button type="submit" name="submitForm" value="create_schema">Пересоздать</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Пересоздать таблицы</legend>
		<button type="submit" name="submitForm" value="create_tables">Пересоздать</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Добавить тестовые данные</legend>
		<button type="submit" name="submitForm" value="insert_test">Добавить</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Добавление</legend>
		в id: <input type="number" name="pid" placeholder="1|2|3..." min="1">
		header: <input type="text" name="header" placeholder="header" value="Новый элемент">
		<button type="submit" name="submitForm" value="add">Добавить</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Выбор предков</legend>
		элемента с id: <input type="number" name="select_ancestors_id" min="1">
		<button type="submit" name="submitForm" value="select_ancestors">Выбрать</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Выбор потомков</legend>
		элемента с id: <input type="number" name="select_descendants_id" min="1">
		<button type="submit" name="submitForm" value="select_descendants">Выбрать</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Переместить элемент</legend>
		id: <input type="number" name="eid" value="10" min="1">
		в id: <input type="number" name="tid" value="9" min="1">
		<button type="submit" name="submitForm" value="move">Переместить</button>
	</fieldset>
</form>
<form action="/php/right.php" method="post" target="frame_right">
	<fieldset>
		<legend>Удалить элемент</legend>
        <label>id: 
            <input type="number" name="id" placeholder="1|2|3...)" min="1">
        </label>
		рекурсивно: <input type="hidden" name="recursively" value="0"><input type="checkbox" name="recursively" value="1">
		<button type="submit" name="submitForm" value="delete">Удалить</button>
	</fieldset>
</form>
<h2>Добавить ордер</h2>
<h2>Добавить тесты!!!</h2>
