<?php
/*
 * 提供自定义排序函数
 * 2009-8-4 rev.1
 * @author Sun Junwen
 * 
 */

/**
 * 按名称排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function cmp_name($a, $b)
{
	return strcmp($a['name'], $b['name']);
}

/**
 * 按名称反向排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function rcmp_name($a, $b)
{
	return strcmp($b['name'], $a['name']);
}

/**
 * 按文件大小排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function cmp_size($a, $b)
{
	if($a['stat']['size'] == $b['stat']['size'])
	{
		return 0;
	}
	else if($a['stat']['size'] > $b['stat']['size'])
	{
		return 1;
	}
	else
	{
		return -1;
	}
}

/**
 * 按文件大小反向排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function rcmp_size($a, $b)
{
	return 0 - cmp_size($a, $b);
}

/**
 * 按最后修改时间排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function cmp_mtime($a, $b)
{
	if($a['stat']['mtime'] == $b['stat']['mtime'])
	{
		return 0;
	}
	else if($a['stat']['mtime'] > $b['stat']['mtime'])
	{
		return 1;
	}
	else
	{
		return -1;
	}
}

/**
 * 按最后修改时间反向排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function rcmp_mtime($a, $b)
{
	return 0 - cmp_mtime($a, $b);
}

/**
 * 按文件扩展名排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function cmp_type($a, $b)
{
	$a_name = $a['name'];
	$a_dot_pos = strrpos($a_name, ".");
	$a_type = "";
	if($a_dot_pos !== false)
	{
		$a_type = substr($a_name, $a_dot_pos + 1, strlen($a_name) - $a_dot_pos - 1);
	}

	$b_name = $b['name'];
	$b_dot_pos = strrpos($b_name, ".");
	$b_type = "";
	if($b_dot_pos !== false)
	{
		$b_type = substr($b_name, $b_dot_pos + 1, strlen($b_name) - $b_dot_pos - 1);
	}
	//echo "$a_type, $b_type\n";

	return strcmp($a_type, $b_type);
}

/**
 * 按文件扩展名反向排序
 * @param $a
 * @param $b
 * @return unknown_type
 */
function rcmp_type($a, $b)
{
	return 0 - cmp_type($a, $b);
}
?>