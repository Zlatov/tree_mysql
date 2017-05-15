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
	$_POST['submitForm']();
}
if (check_schema_select()) {
	if (check_tables_exist()) {
		$flat = get_items();
		$nested = Tree::to_nested($flat);
		$html = Tree::to_html($nested, ['tpl_li' => '<li>??header?? <small>#??id??</small> <small>^??level??</small> <small>↓??order??</small>']);
		echo $html;
	}
}

require_once 'right_footer.php';
