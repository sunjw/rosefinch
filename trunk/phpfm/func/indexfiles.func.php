<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";
require_once "../inc/search.class.php";
require_once "../log/log.func.php";

/*
 * 这是实验索引构建
 * 之后需要整理并移入 Search.class.php 内
 * 存入数据库的所有数据都是 UTF-8 编码的
 */ 

$search = new Search();

$search->create_index();

?>