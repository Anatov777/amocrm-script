<?php
$subdomain = 'anatovtimur'; //Поддомен нужного аккаунта
$link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

/** Соберем данные для запроса */
$data = [
	'client_id' => '20c26aff-fc76-4fe6-b91e-27d2cd2752ac',
	'client_secret' => 'fI94kBLLpW3Sl62NFDSAUHfdhdGY06kOnTpdiGlx5cSBzJzriRaBS2IeqOVDPGD4',
	'grant_type' => 'authorization_code',
	'code' => 'def50200012845c338c6e79d6b296ba2f9c643a98459b42f0b6dc86da13a60b1003365d6bd358c8fea408b0024101ab618b17770e3d01255f4f9e702643462b9e4c5c43be5f96c9f6f422f3cf085fbe43aab2a2781cb0c8362727081f83e874b76fce9dd1dba3f7cd8c37f782a6b9d66a1522ce9fa655225757f04eae2bd1565e3c040ccf8a6cc44c3b2f9cfffa0b16ad698e25d97c452116f9486da7031c0954fff746d017dad233fec758b8b7c97dacd3c002f6e5201be6dd9bf93188bc3ee5afe056ea2c51b46161040ad411b26963870467a9ad47238f1d267c6da2b7111ccf08ae131273c7a612d812b24895c9754bc1303e68433d7d605a690ab323a3afa76004f726e849768b51e5cfb573a984c0e8b00ec200b503456e607c8d2776572ff350725c799e799c934203a324830797a8e399813ef7031eb8e822295aa4d8989abaf60f3c409f061ccb8b34959bada5b505c8fbca8983ed3848ac09a70a2e5df2573dd21e262bd18679f7fff2e8ff8fd25721aca58bdac3f10d5ee06eb9c141035a62499c35f2fef3cb9d63975663f85e8b22b19949f66bd6a8873c8091fe2231552a0a137a74aa3733ba5d37baead24fa446dce7ec42e8f1b1e6e67bc562bded119a0a00c374c',
	'redirect_uri' => 'https://anatovtimur.amocrm.ru/',
];

/**
 * Нам необходимо инициировать запрос к серверу.
 * Воспользуемся библиотекой cURL (поставляется в составе PHP).
 * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
 */
$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
/** Устанавливаем необходимые опции для сеанса cURL  */
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code = (int)$code;
$errors = [
	400 => 'Bad request',
	401 => 'Unauthorized',
	403 => 'Forbidden',
	404 => 'Not found',
	500 => 'Internal server error',
	502 => 'Bad gateway',
	503 => 'Service unavailable',
];

try
{
	/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
	if ($code < 200 || $code > 204) {
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
	}
}
catch(\Exception $e)
{
	die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

/**
 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
 * нам придётся перевести ответ в формат, понятный PHP
 */
$response = json_decode($out, true);

$access_token = $response['access_token']; //Access токен
$refresh_token = $response['refresh_token']; //Refresh токен
$token_type = $response['token_type']; //Тип токена
$expires_in = $response['expires_in']; //Через сколько действие токена истекает

echo $access_token;

?>