<?php
class Sudoku{
	public $ar;									//массив решений
	public $arCandidate;						//массив кандидатов
	
	//private $arSnapshot = array();				//стек массива решений
	//private $arCandidateSnapshot = array();		//стек массива кандидатов
	private $forecastDepth;						//глубина прогноза
	private $forecastDepthMax = 5;				//максимальная глубина прогноза
	private $arCollation;						//сравнительный массив решений
	private $arCollationPrime;					//основной сравнительный массив решений
	private $blockPositionI;					//позиция (i) выбранного блока
	private $blockPositionJ;					//позиция (j) выбранного блока
	private $blockPosition = array(11, 14, 17, 41, 44, 47, 71, 74, 77);
	private $testError;							//№ ошибки теста
	
	
	function __construct($ar)
	{
		if($this->Test(false, $ar)) $this->ar = $ar;
	}
	public function Start()
	{
		$this->Lone(); $this->SearchForSolutions();
		if(!$this->Test(true, $this->ar)) 
			if(!$this->Forecast()) $this->Err("Решений не найдено!");
	}
	private function SearchForSolutions()	//Поиск решений
	{
		do{ $this->arCollationPrime = $this->ar; $this->LoneHidden();}
		while($this->arCollationPrime != $this->ar);
	}
	private function Forecast()				//Стратегия "Прогноз"
	{
		/* Если с помощью стандартных стратегий решение не найдено, находим блок с минимальным кол-вом
		кандидатов и продолжаем поиск решений, сделав прогноз на одном из полей.
		В случае если решения не обнаруживаютя - меняем прогноз и продолжаем поиск. */
		
		$arSnapshot = $this->ar; $arCandidateSnapshot = $this->arCandidate;
		
		foreach($this->arCandidate as $key => $val) if(count($val) == 2) $arForecast[$key] = $val;
		
		if($arForecast)
		{
			foreach($arForecast as $key => $val)
			{
				echo $q; $q++;
				while(count($val)) 
				{
					$this->ar[$key] = array_shift($val); 
					$this->DelCandidate((int)substr($key, 0), (int)substr($key, 1));
					$this->SearchForSolutions();
					if(!$this->Test(true, $this->ar))
						switch($this->testError)
						{
							case 1: //Неверное решение! Сделать другое предположение.
								$this->ar = $arSnapshot; $this->arCandidate = $arCandidateSnapshot;
							break;
							case 2: //Сделать следующее предположение
								if($this->Forecast()) return true;
								else { $this->ar = $arSnapshot; $this->arCandidate = $arCandidateSnapshot; }
							break;
						}
					else return true; //Решение найдено!
				}
				$this->ar = $arSnapshot; $this->arCandidate = $arCandidateSnapshot;
			}
		}
		return false;
		
		/* for($this->forecastDepth = 1; $this->forecastDepth <= $this->forecastDepthMax; $this->forecastDepth++)
		} */
	}
	
	private function Test($f, $ar)			//Тест заполнения и решения
	{
		for($i = 1; $i < 10; $i++)
		{
			$mc = array(); $ml = array(); $mb = array();
			
			for($j = 1; $j < 10; $j++)
			{
				$mc[] = $ar[$i.$j]; $ml[] = $ar[$j.$i]; 
			
				if(in_array($i.$j, $this->blockPosition))
					for($m = 0; $m < 3; $m++)
						for($n = 0; $n < 3; $n++) { $im = $i + $m; $jn = $j + $n; $mb[] = $ar[$im.$jn]; }
				
				if(!eregi("^[1-9]+$", $ar[$j.$i]) && $ar[$j.$i] != "") 
					$this->Err("Недопустимый символ: ".$ar[$j.$i]);
			}
			
			$mbChunk = array_chunk($mb, 9); 
			$clb = array("строке: ".$i => $mc, "столбце: ".$i => $ml);
			foreach($mbChunk as $k => $v) { $key = $k + $i; $clb["блоке: ".$key] = $v; }
			foreach ($clb as $k => $v)
				foreach(array_count_values($v) as $key => $val)
					if ($val > 1 && $key != "")
					{ 
						if($f) { $this->testError = 1; return false; }
						else $this->Err("Одинаковые значения в".$k); 
					}
		}
		if($f)
		{
			foreach($ar as $val) if($val == "") { $this->testError = 2; return false; }
		}
		return true;
	}
	
	//Стратегии
	private function Lone()					//Создание массива кондидатов, стратегия "Одиночки"
	{
		/* Метод заключается в отыскании в таблице одиночек, т.е. ячеек, в которых возможна только одна цифра и никакая другая. Записываем эту цифру в данную ячейку и исключаем ее из других клеток этой строки, столбца и блока. */
		do
		{
			$this->arCollation = $this->ar;
			$this->arCandidate = array(); 
			for($i = 1; $i < 10; $i++)
			{
				for($j = 1; $j < 10; $j++)
				{
					if($this->ar[$i.$j] == "")
					{
						$this->Intersection ($i,$j);
						for($k = 1; $k < 10; $k++) $possibleVal[$k] = $k;
						
						foreach($possibleVal as $key => $val)
							if(in_array($val, $this->rezInter)) unset($possibleVal[$key]);
						
						if(count($possibleVal) == 1)
						{
							$this->ar[$i.$j] = array_keys($possibleVal)[0];
							$this->DelCandidate($i, $j);
						}
						else if(count($possibleVal) != 0)
							$this->arCandidate[$i.$j] = $possibleVal;
					}
				}
			}
		}while($this->arCollation != $this->ar);
		
	}
	private function LoneHidden()			//Стратегия "Скрытые одиночки"
	{
		/* Если в ячейке стоит несколько кандидатов, но один из них не встречается больше ни в одной другой ячейке данной строки (столбца или блока), то такой кандидат называется «скрытой одиночкой».  */
		do
		{
			$this->arCollation = $this->ar;
			for($i = 1; $i < 10; $i++)
			{
				for($j = 1; $j < 10; $j++)
				{
					if($this->ar[$i.$j] == "")
					{
						$this->ArraysCandidate($i,$j);
						
						$cAr = Array(); $lAr = array(); $bAr = array();
						foreach(array_count_values($this->arColumn) as $k => $v) $cAr[] = $k;
						foreach(array_count_values($this->arLine) as $k => $v) $lAr[] = $k;
						foreach(array_count_values($this->arBlock) as $k => $v) $bAr[] = $k;
						
						foreach($this->arCandidate[$i.$j] as $val)
						{
							if(!in_array($val, $cAr)||!in_array($val, $lAr)||!in_array($val, $bAr))
								{ $this->ar[$i.$j] = $val; $this->DelCandidate($i, $j); }
						}
					}
				}
			}
		}while($this->arCollation != $this->ar); $this->OpenPairs();
	}
	private function OpenPairs()			//Стратегия "Открытые пары"
	{
		/* Если две ячейки в группе (строке, столбце, блоке) содержат идентичную пару кандидатов и ничего более, то никакие другие ячейки этой группы не могут иметь значения этой пары. Эти 2 кандидата могут быть исключены из других ячеек в группе.  */
		do
		{
			$this->arCollation = $this->ar;
			for($i = 1; $i < 10; $i++)
			{
				for($j = 1; $j < 10; $j++)
				{
					if($this->arCandidate[$i.$j] && count($this->arCandidate[$i.$j]) == 2)
					{
						
						//-------------------------------------
						for($k = 1; $k < 10; $k++) 
							if($k != $i && $this->arCandidate[$i.$j] == $this->arCandidate[$k.$j]){
								for($m = 1; $m <10; $m++)
								{
									if($m != $i && $m != $k && $this->arCandidate[$m.$j])
									{
										foreach($this->arCandidate[$i.$j] as $val)
											unset($this->arCandidate[$m.$j][array_search($val, $this->arCandidate[$m.$j])]); 
										
										switch(count($this->arCandidate[$m.$j]))
										{
											case 0: $this->Err("Нет кандидатов для поля: ".$m.$j); break;
											case 1: 
												$this->ar[$m.$j] = $this->arCandidate[$m.$j][array_keys($this->arCandidate[$m.$j])[0]];
												$this->DelCandidate($m,$j);
											break;
										}
									}
								}
								break;
							}
						//-------------------------------------
						for($k = 1; $k < 10; $k++) 
							if($k != $j && $this->arCandidate[$i.$j] == $this->arCandidate[$i.$k])
							{
								for($m = 1; $m <10; $m++)
								{
									if($m != $j && $m != $k && $this->arCandidate[$i.$m])
									{
										foreach($this->arCandidate[$i.$j] as $val)
											unset($this->arCandidate[$i.$m][array_search($val, $this->arCandidate[$i.$m])]); 
										
										switch(count($this->arCandidate[$i.$m]))
										{
											case 0: $this->Err("Нет кандидатов для поля: ".$i.$m); break;
											case 1: 
												$this->ar[$i.$m] = $this->arCandidate[$i.$m][array_keys($this->arCandidate[$i.$m])[0]];
												$this->DelCandidate($i,$m); 
											break;
										}
									}
								}
								break;
							}
						//-------------------------------------
						$this->BlockPosition($i,$j);
						for($ki = $this->blockPositionI; $ki < $this->blockPositionI + 3; $ki++)
							for($kj = $this->blockPositionJ; $kj < $this->blockPositionJ + 3; $kj++)
								
								if($i.$j != $ki.$kj && $this->arCandidate[$i.$j] == $this->arCandidate[$ki.$kj])
								{
									
									for($mi = $this->blockPositionI; $mi < $this->blockPositionI + 3; $mi++)
									{
										for($mj = $this->blockPositionJ; $mj < $this->blockPositionJ + 3; $mj++)
										{
											if($mi.$mj != $i.$j && $mi.$mj != $ki.$kj && $this->arCandidate[$mi.$mj])
											{
												foreach($this->arCandidate[$i.$j] as $val)
													unset($this->arCandidate[$mi.$mj][array_search($val, $this->arCandidate[$mi.$mj])]); 
												
												switch(count($this->arCandidate[$mi.$mj]))
												{
													case 0: $this->Err("Нет кандидатов для поля: ".$mi.$mj); break;
													case 1: 
														$this->ar[$mi.$mj] = $this->arCandidate[$mi.$mj][array_keys($this->arCandidate[$mi.$mj])[0]];
														$this->DelCandidate($mi,$mj); 
													break;
												}
											}
										}
									}
									break 2;
								}
					}
				}
			}
		}while($this->arCollation != $this->ar); 
	}
	
	//Вспомогательные функции
	private function Intersection ($i,$j){		//Возвращает набор не возможных значений для точки пересечения
		$this->rezInter = array();
		for ($k = 1; $k < 10; $k++){
			$this->rezInter[] = $this->ar[$k.$j]; 
			$this->rezInter[] = $this->ar[$i.$k];
		}
		//добавляем значение из блока
		$this->BlockPosition($i,$j);
		for($ki = $this->blockPositionI; $ki < $this->blockPositionI + 3; $ki++)
			for($kj = $this->blockPositionJ; $kj < $this->blockPositionJ + 3; $kj++)
				$this->rezInter[] = $this->ar[$ki.$kj];
	}
	private function Err($e){					//Отправка ошибки
		$n = array("f" => 1, "e" => $e);
		echo json_encode($n); exit();
	}
	private function DelCandidate($i,$j){		//удаление кандидатов при определении значения
		
		//удаляем кандидатов в определившемся поле
		unset($this->arCandidate[$i.$j]);
		//удаляем значение из кандидатов в столбце и строке
		for($k = 1; $k < 10; $k++){
			if($this->arCandidate[$k.$j] && in_array($this->ar[$i.$j], $this->arCandidate[$k.$j]))
			{
				unset($this->arCandidate[$k.$j][array_search($this->ar[$i.$j], $this->arCandidate[$k.$j])]); $this->OneCandidate($k,$j);
			}
			if($this->arCandidate[$i.$k] && in_array($this->ar[$i.$j], $this->arCandidate[$i.$k]))
			{
				unset($this->arCandidate[$i.$k][array_search($this->ar[$i.$j], $this->arCandidate[$i.$k])]); $this->OneCandidate($i,$k);
			}
		}
		//удаляем значение из кандидатов в блоке
		$this->BlockPosition($i,$j);
		for($ki = $this->blockPositionI; $ki < $this->blockPositionI + 3; $ki++)
			for($kj = $this->blockPositionJ; $kj < $this->blockPositionJ + 3; $kj++)
				if($this->arCandidate[$ki.$kj] && in_array($this->ar[$i.$j], $this->arCandidate[$ki.$kj]))
				{
					unset($this->arCandidate[$ki.$kj][array_search($this->ar[$i.$j], $this->arCandidate[$ki.$kj])]); $this->OneCandidate($ki,$kj);
				}
	}
	private function OneCandidate($i,$j){		//Рекурсия выполняется когда остается один кандидат
		if(count($this->arCandidate[$i.$j]) == 1) {
			$this->ar[$i.$j] = array_keys($this->arCandidate[$i.$j])[0];
			$this->DelCandidate($i, $j);
		}
		else if(count($possibleVal) != 0) 
			$this->Err("Ошибка, для поля ".$i.$j." не может быть определено значение!");
	}
	private function ArraysCandidate($i,$j){	//Строка, столбец и блок кандидаты и найденые значения
		$this->arColumn = array(); 
		$this->arLine = array(); 
		$this->arBlock = array();
			//-------------------------------------
		for($k = 1; $k < 10; $k++) 
			if($k != $i) {
				if($this->ar[$k.$j] != "") $this->arLine[] = $this->ar[$k.$j];
				else foreach($this->arCandidate[$k.$j] as $val) array_push($this->arLine, $val);
			}
			//-------------------------------------
		for($k = 1; $k < 10; $k++) 
			if($k != $j) {
				if($this->ar[$i.$k] != "") $this->arColumn[] = $this->ar[$i.$k];
				else foreach($this->arCandidate[$i.$k] as $val) array_push($this->arColumn, $val);
			}
			//-------------------------------------
		$this->BlockPosition($i,$j);
		for($ki = $this->blockPositionI; $ki < $this->blockPositionI + 3; $ki++)
			for($kj = $this->blockPositionJ; $kj < $this->blockPositionJ + 3; $kj++)
				if($i.$j != $ki.$kj){
					if($this->ar[$ki.$kj] != "") $this->arBlock[] = $this->ar[$ki.$kj];
					else foreach($this->arCandidate[$ki.$kj] as $val) array_push($this->arBlock, $val);
				}
	}
	private function BlockPosition($i,$j){		//Рассчет позиции лев. верх. угла блока
		for($k = 2; $k < 10; $k += 3){
			if(abs($k - $i)<2) $this->blockPositionI = $k - 1;
			if(abs($k - $j)<2) $this->blockPositionJ = $k - 1;
		}
	}

}

















?>