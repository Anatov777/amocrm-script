<?php
$root=__DIR__.DIRECTORY_SEPARATOR;
require $root.'functions.php';
$subdomain = 'anatovtimur';
$link='https://'.$subdomain.'.amocrm.ru/api/v4/leads';
$access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImEwOGNkZjFjOTY3MTc4MmE4ZDBmNWU5YjAzNTY2M2IwM2UzZDJmNjg4MTlmYTFjYmRlNzA5ZTA1M2IxMGFkMmVhMzRjZmFhNTRmZDdkOGU1In0.eyJhdWQiOiIyMGMyNmFmZi1mYzc2LTRmZTYtYjkxZS0yN2QyY2QyNzUyYWMiLCJqdGkiOiJhMDhjZGYxYzk2NzE3ODJhOGQwZjVlOWIwMzU2NjNiMDNlM2QyZjY4ODE5ZmExY2JkZTcwOWUwNTNiMTBhZDJlYTM0Y2ZhYTU0ZmQ3ZDhlNSIsImlhdCI6MTYwMDgxMjE0NSwibmJmIjoxNjAwODEyMTQ1LCJleHAiOjE2MDA4OTg1NDUsInN1YiI6IjYzMzQzNjYiLCJhY2NvdW50X2lkIjoyOTA0NjI1MCwic2NvcGVzIjpbInB1c2hfbm90aWZpY2F0aW9ucyIsImNybSIsIm5vdGlmaWNhdGlvbnMiXX0.FZrvIcVbWUioaYMzO9WFAYtB2tIm41L1Huw-ZIs1dViQzESflxgHxv3lbTvUBkAl5uS41IqI1TPENRg6sSMLBekBGmPDyPyNFQD07w1t5nR3hWb5yWEMHPrHYLf7pY_JFF3oQTp23byfd264W3g6cVi8wwrWBkd5XC5CSi0ShHu49yi8ALin4bjDi2fjKRzp3NRtuxhRRXameDFNq4Q33-81BpRyJFRPC-PELLaZJhzi0IiA1j8rt3BgED0l7DCErHYXXVKdVvdSLMJoY0-ObTfrSH2O5nEnBGqPCiVwzQhytzaj87nNgjKiRhlelPo3XupLFdCEGgpEWDhR1EwneQ';
$headers = [
	'Authorization: Bearer ' . $access_token
];
$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
/** Устанавливаем необходимые опции для сеанса cURL  */
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code = (int)$code;
CheckCurlResponse($code);
$Response=json_decode($out,true);
$leads_list=$Response['_embedded']['leads'];
$leads_id = array();

// Проверяем, есть ли доступ к данным и записываем в массив ID сделок, у которых нет задач.
if(isset($leads_list)) {
	
	foreach($leads_list as $leads) {
		if(is_array($leads) && isset($leads['id'])) {
			if ($leads['closest_task_at'] == 0) {
				array_push($leads_id, $leads['id']);
			}
		} else {
			die('Невозможно получить поле "ID сделки"');
		} 
	}

} else {
	die('Невозможно получить "Список сделок"');
}

$tasks['_embedded']['tasks']=array();

foreach($leads_id as $id) {
	array_push($tasks['_embedded']['tasks'],
		array(
			'entity_id'=>$id, #ID сделки
			'entity_type'=>'leads', #Показываем, что это - сделка, а не контакт
			'task_type_id'=>1, #Тип задачи - звонок
			'text'=>'Сделка без задачи',
			'complete_till'=>strtotime('24-09-2020') #Дата до которой необходимо завершить задачу.
		)
	);
}

#Формируем ссылку для запроса
$link='https://'.$subdomain.'.amocrm.ru/api/v4/tasks';

$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($tasks['_embedded']['tasks']));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);

$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

CheckCurlResponse($code); #Проверка кода ответа сервера

$Response=json_decode($out,true);
$Response=$Response['_embedded']['tasks'];

$output='ID добавленных задач:'.PHP_EOL;

foreach($Response as $v)
  if(is_array($v))
    $output.=$v['id'].PHP_EOL;

echo $output;

?>