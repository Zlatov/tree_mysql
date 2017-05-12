<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>CT</title>
	<style type="text/css">
		.tree { padding-left:25px; }
		.tree ul { margin:0; padding-left:6px; }
		.tree li { position:relative; list-style:none outside none; border-left:solid 1px #999; margin:0; padding:0 0 0 19px; line-height:23px; }
		.tree li:before { content:''; display:block; border-bottom:solid 1px #999; position:absolute; width:18px; height:11px; left:0; top:0; }
		.tree li:last-child { border-left:0 none; }
		.tree li:last-child:before { border-left:solid 1px #999; }		
	</style>
</head>
<body>
<h1><a href="">ct</a></h1>
<h2>Добавить ордер</h2>
<h2>Добавить тесты!!!</h2>
<form method="post">
	<fieldset>
		<legend>Пересоздать базу <?= $config['dbName'] ?></legend>
		<button type="submit" name="submitForm" value="create_schema">Пересоздать</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Пересоздать таблицы</legend>
		<button type="submit" name="submitForm" value="create_tables">Пересоздать</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Добавить тестовые данные</legend>
		<button type="submit" name="submitForm" value="insert_test">Добавить</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Добавление</legend>
		в id: <input type="text" name="pid" placeholder="pid (0|1|..)" value="5">
		header: <input type="text" name="header" placeholder="header" value="Новый элемент">
		<button type="submit" name="submitForm" value="add">Добавить</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Выбор предков</legend>
		элемента с id: <input type="text" name="select_ancestors_id">
		<button type="submit" name="submitForm" value="select_ancestors">Выбрать</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Выбор потомков</legend>
		элемента с id: <input type="text" name="select_descendants_id">
		<button type="submit" name="submitForm" value="select_descendants">Выбрать</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Переместить элемент</legend>
		id: <input type="text" name="eid" value="10">
		в id: <input type="text" name="tid" value="9">
		<button type="submit" name="submitForm" value="move">Переместить</button>
	</fieldset>
</form>
<form method="post">
	<fieldset>
		<legend>Удалить элемент</legend>
        <label>с id:
            <input type="text" name="delid" placeholder="pid (0|1|..)" value="5">
        </label>
		рекурсивно: <input type="hidden" name="recursively" value="0"><input type="checkbox" name="recursively" value="1">
		<button type="submit" name="submitForm" value="del">Удалить</button>
	</fieldset>
</form>
