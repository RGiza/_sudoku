<?php
	if(!$_POST){//Загрузка формы ввода
		$t = '<!DOCTYPE html>
				<html xmlns="http://www.w3.org/1999/xhtml" lang="ru-RU">	
				<head>		
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta content="IE=edge" http-equiv="X-UA-Compatible">
					<title>Судоку</title>
					<meta name="description" content="Тестовое задание на воканисию веб-программист" />
					<meta name="keywords" content="судоку" />
					
					<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js" charset="utf-8"></script>
					<script type="text/javascript" src="sudoku.js"></script>
					
					<link rel="stylesheet" href="sudoku.css" type="text/css" media="screen" />
					<link rel="icon" href="sudoku.ico" type="image/x-icon.ico">
					<link rel="shortcut icon" href="sudoku.ico" type="image/x-icon.ico">
				</head>';
		$t .= "<body><table>"; $cc = "";
		for($i=1; $i<10; $i++){
			$t .= "<tr>"; $cc1 = "";
			switch ($i){
				case 3: case 6: $cc1 = "_b"; break;
				case 4: case 7: $cc1 = "_t"; break;
			}
			for($j=1; $j<10; $j++) {
				$cc2 = "";
				switch ($j){
					case 3: case 6: $cc2 = " _r"; break;
					case 4: case 7: $cc2 = " _l"; break;
				}
				$t .= "<td class='".$cc1.$cc2."' id = '".$i.$j."'></td>"; 
			}
			$t .= "</tr>";
		}
		$t .= "</table><div id = 'd'>Решить: Enter<br>Перемещение: &lArr;&uArr;&rArr;&dArr;<br>Стереть символ: Delete<br><button onclick='LoadTZ()'>Установить значения ТЗ</button>&nbsp;<button onclick='Clean()'>Отчистить</button>&nbsp;<button onclick='Step()'>Решить</button></div></body>";
		echo $t;
	}
	else{
		include_once('class.php');
		 $ex = new Sudoku($_POST); $ex->Start(); 
		
		$n = array("f"=>0, "ar"=>$ex->ar, "arCandidate"=>$ex->arCandidate);
		echo json_encode($n);
	}
?>




































