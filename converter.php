<?php
/* ==============================================================
 * 单位变化，单位数值转换脚本
 * Author: Rick Chen
 * Date: 2016-7-19
 * Manual: php.exe 这个脚本文件名称 欲转换文件名 比率 原单位 新单位
 * 关于比率: 如果a原单位 = b新单位，那么比率=b/a;
 * ==============================================================
 *

/*
 * 修正字符串末尾数值参数
 */
function fixParam($str, $ratio, $match_unit, $target_unit, $isFinal = FALSE) {
	// 获取字符串末尾下标
	$posEnd = strlen($str) - 1;
	// 初始化数字首下标为末尾下标
	$posStart = strlen($str) - 1;
	// 已经开始扫描数字的标识
	$flag = FALSE;
	for ($i = $posEnd; $i >= 0;$i --){
		if ($str[$i] == ' ') {
			// 如果是空格并且已经开始数字扫描，那么跳过
			if ($flag) {
				break;
			}
			// 还未进行数字扫描，继续循环
			continue;
		}
		// 判断是不是数字
		if (($str[$i] >= '0' && $str[$i] <= '9') || $str[$i] == '.') {
			// 没开始数字扫描，则记录末尾下标，并开启数字扫描
			if (!$flag){
				$posEnd = $i;
				$flag = TRUE;
			}
			// 记录首部
			$posStart = $i;
		}else{
			break;
		}
	}
	// 如果数字首尾下标相同，并且没有扫描到数字
	if (!$flag && $posEnd == $posStart) {
		// 直接返回原字符串
		if ($isFinal) {
			return $str;
		}
		return $str.$match_unit;
	}
	// 取出末尾数字部分
	$valueStr = substr($str, $posStart, $posEnd - $posStart + 1);
	// 转化为浮点数
	$value = floatval($valueStr);
	// 保留小数点后两位（不进行四舍五入）
	$value = floor($value *= $ratio * 100)/100;
	// 将新数值和单位拼接到新字符串尾部
	$result_str = substr($str, 0, $posStart);
	$result_str .= $value.$target_unit;
	return $result_str;
}
// 文件名称
$file_name = $argv[1];
// 转换比率
$ratio = $argv[2];
// 匹配字符串
$match_unit = $argv[3];
// 替换字符串
$target_unit = $argv[4];

// 打开文件
@ $file = fopen($file_name, 'r');
if (!$file) {
	// 不存在文件或者没有访问权限则提示并退出
	echo "No such file or permission denied.\n";
	exit;
}
// 新建文件（需要文件夹权限），新文件名称为原文件名加上波浪符号（'~'）
@ $new_file = fopen($file_name.'~', 'w');
if (!$new_file) {
	echo "Permission denied.\n";
}
$content = '';
// 读取文件全部内容
while (!feof($file)) {
	$line = fgets($file);
	$content .= $line;
}
// echo mb_strlen($content)."\n";
// 按照匹配字符串将原字符串分割为数组
$str_array = explode($match_unit, $content);
// 数组长度
$num = count($str_array);
// 初始化结果字符串
$result = '';
// 逐个处理单位转换，并拼接到结果字符串
for ($i = 0; $i < $num; $i ++) {
	$str_array[$i] = fixParam($str_array[$i], $ratio, $match_unit,
	   	$target_unit, $i == $num - 1);
	$result .= $str_array[$i];
}
// echo $result;
// 写入结果字符串
fwrite($new_file, $result);
// 关闭文件
fclose($file);
fclose($new_file);
