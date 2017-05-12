<?php
require_once "vendor/autoload.php";
use Zlatov\Tree\Tree;

include_once 'php/config.php';
include_once 'php/common.php';

include_once 'php/header.php';

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
		$html = Tree::to_html($nested, ['tpl_li' => '<li>??header?? <small>#??id??</small> <small>^??level??</small>']);
		echo $html;
	}
}

include_once 'php/footer.php';
