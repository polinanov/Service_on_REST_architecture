<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Пример веб-страницы</title>
 </head>
 <body>
    <h1>Лабораторная работа 1</h1>
<?php
	if(isset($_GET['lan']) && isset($_GET['lon'])){
		echo "Запрос получен<br>";
		$lan = is_numeric($_GET['lan']);
		$lon = is_numeric($_GET['lon']);
		if(($lan && $lon) == true){
			$url = "https://api.darksky.net/forecast/698e2ee903cae2a608ab76e29e34ee50/" . $_GET['lan'] ."," . $_GET['lon'] . "?exclude=hourly&units=si";
		//	echo $url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);//"https://api.darksky.net/forecast/698e2ee903cae2a608ab76e29e34ee50/55.670686,38.004099?exclude=hourly&units=si");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$output = curl_exec($ch);
		//	echo $output;
			$output = json_decode($output);
			echo "Город: " . $output->timezone . "<br>";
			echo "Единицы измерения: Celsius<br>";
			
			echo "Температура: " . $output->currently->temperature . "<br>";
			curl_close($ch);
			}else{
				echo "Введите коректный запрос в виде ..weather.php?lan=55&lon=38, где lan и lon являются цифрами<br>";
			}
			

	}
	else{
		echo "Введите коректный запрос в виде ..weather.php?lan=55&lon=38<br>";
	}
?>
 </body>
</html>