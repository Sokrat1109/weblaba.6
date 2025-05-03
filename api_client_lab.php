<?php
// Файл: api_client_lab.php

/**
 * ApiClient — класс для работы с API через cURL
 */
class ApiClient {
    private string $baseUrl;
    private array $headers;

    public function __construct(string $baseUrl, array $headers = []) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = $headers;
    }

    private function request(string $method, string $endpoint, array $data = [], array $queryParams = []) {
        $url = $this->baseUrl . $endpoint;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $headers = array_merge(
            ['Content-Type: application/json'],
            $this->headers
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($statusCode >= 400) {
            throw new Exception("HTTP Error: $statusCode | Response: $response");
        }

        return json_decode($response, true);
    }

    public function get(string $endpoint, array $queryParams = []) {
        return $this->request('GET', $endpoint, [], $queryParams);
    }

    public function post(string $endpoint, array $data = []) {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = []) {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint) {
        return $this->request('DELETE', $endpoint);
    }
}

// ========== ТЕСТИРОВАНИЕ ==========

$client = new ApiClient('https://jsonplaceholder.typicode.com');

// 1. GET
echo "\n--- GET /posts/1 ---\n";
$response = $client->get('/posts/1');
print_r($response);

// 2. POST
echo "\n--- POST /posts ---\n";
$response = $client->post('/posts', [
    'title' => 'foo',
    'body' => 'bar',
    'userId' => 1
]);
print_r($response);

// 3. PUT
echo "\n--- PUT /posts/1 ---\n";
$response = $client->put('/posts/1', [
    'id' => 1,
    'title' => 'updated',
    'body' => 'new content',
    'userId' => 1
]);
print_r($response);

// 4. DELETE
echo "\n--- DELETE /posts/1 ---\n";
$response = $client->delete('/posts/1');
print_r($response);

// 5. GET с заголовками и параметрами
echo "\n--- GET с параметрами ?userId=1 ---\n";
$response = $client->get('/posts', ['userId' => 1]);
print_r($response);
