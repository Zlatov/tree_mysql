<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Zlatov\Tree\Tree;

require_once 'config.php';
require_once 'common.php';

require_once 'right_header.php';

if (check_schema_exist()) {
	schema_select();
}
if (isset($_POST['submitForm'])) {
	echo '<p><strong>Результат выбранного действия:</strong></p>';
	$_POST['submitForm']();
}
if (check_schema_select()) {
	if (check_tables_exist()) {
		echo "<p><strong>Дерево:</strong></p>";
		$count = get_count();
		if ($count>30000) {
			echo "<p>Браузер не выведет такое количество элементов ($count).</p>";
		} else {
			$flat = get_all();
			if (count($flat)===0) {
				$html = "<p>Нет элементов.</p>";
			} else {
				$nested = Tree::to_nested($flat);
				$html = Tree::to_html($nested, ['tpl_li' => '<li>??header?? <small>#??id??</small> <small>^??level??</small> <small>↓??order??</small>']);
			}
			echo $html;
		}
	}
} else {
    echo "<p>База данных не выбрана.</p>";
}

require_once 'right_footer.php';
