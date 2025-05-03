<?php

class ApiClient {
    // базовый урл для апи
    private string $baseUrl;
    // заголовки для запросов
    private array $headers;

    //принимает базовый урл и дополнительные заголовки
    public function __construct(string $baseUrl, array $headers = []) {
        $this->baseUrl = rtrim($baseUrl, '/');
        //сохраняем переданные заголовки
        $this->headers = $headers;
    }

    //метод для выполнения хттп запросов
    private function request(string $method, string $endpoint, array $data = [], array $queryParams = []) {
        // полный урл, добавляя параметры запроса
        $url = $this->baseUrl . $endpoint;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        // сУРЛ сессия
        $ch = curl_init();
        // урл для сУРЛ
        curl_setopt($ch, CURLOPT_URL, $url);
        // результат запроса возвращаем в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // метод запроса
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // если метод не ГЕТ
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // объединение стандартного заголовка с переданными
        $headers = array_merge(
            ['Content-Type: application/json'],
            $this->headers
        );

        // заголовки для запроса
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // запрос и сохранение результата
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // ошибка при выполнении запроса
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        // закрываем сУРЛ сессию
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new Exception("HTTP Error: $statusCode | Response: $response");
        }

        return json_decode($response, true);
    }

    // выполнение ГЕТ запроса
    public function get(string $endpoint, array $queryParams = []) {
        return $this->request('GET', $endpoint, [], $queryParams);
    }

    // ПОСТ запрос
    public function post(string $endpoint, array $data = []) {
        return $this->request('POST', $endpoint, $data);
    }

    // ПУТ запрос
    public function put(string $endpoint, array $data = []) {
        return $this->request('PUT', $endpoint, $data);
    }

    // ДЕЛИТ запрос
    public function delete(string $endpoint) {
        return $this->request('DELETE', $endpoint);
    }
}



$client = new ApiClient('https://jsonplaceholder.typicode.com');

// 1 ГЕТ
echo "\n--- GET /posts/1 ---\n";
$response = $client->get('/posts/1');
print_r($response);

// 2 ПОСТ
echo "\n--- POST /posts ---\n";
$response = $client->post('/posts', [
    'title' => 'foo',
    'body' => 'bar',
    'userId' => 1
]);
print_r($response);

// 3 ПУТ
echo "\n--- PUT /posts/1 ---\n";
$response = $client->put('/posts/1', [
    'id' => 1,
    'title' => 'updated',
    'body' => 'new content',
    'userId' => 1
]);
print_r($response);

// 4 ДЕЛИТ
echo "\n--- DELETE /posts/1 ---\n";
$response = $client->delete('/posts/1');
print_r($response);

// 5 ГЕТ с заголовками и параметрами
echo "\n--- GET с параметрами ?userId=1 ---\n";
$response = $client->get('/posts', ['userId' => 1]);
print_r($response);

