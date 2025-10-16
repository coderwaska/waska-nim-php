<?php

namespace Wastukancana\Provider;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Wastukancana\StudentProviderInterface;

class WastuFyi implements StudentProviderInterface
{
    private Client $http;

    private string $nim;

    private ?string $name = null;

    private ?string $gender = null;

    private ?bool $isGraduated = null;

    public function __construct(string $nim, string $bearerToken, ?Client $client = null)
    {
        $this->http = $client ?? new Client([
            'base_uri' => 'https://api.wastu.fyi',
            'verify' => false,
            'headers' => ['Authorization' => 'Bearer '.$bearerToken],
        ]);
        $this->nim = ltrim($nim, '0');
    }

    private function fetchData(string $endpoint): ?object
    {
        try {
            $response = $this->http->request('GET', $endpoint);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            return null;
        }
    }

    private function parseResponse(ResponseInterface $response): ?object
    {
        $content = $response->getBody()->getContents();

        return json_decode($content);
    }

    private function prepare(): void
    {
        if ($this->name !== null || $this->gender !== null || $this->isGraduated !== null) {
            return;
        }

        $response = $this->fetchData('students/detail?student_id='.$this->nim);

        if (! $response) {
            return;
        }

        $data = $response->data ?? null;

        if (! $data) {
            return;
        }

        $status = isset($data->status) ? strtolower((string) $data->status) : null;

        $this->name = $data->name ?? null;
        $this->gender = $data->gender ?? null;
        $this->isGraduated = $status !== null ? ($status === 'lulus') : null;
    }

    public function getName(): ?string
    {
        $this->prepare();

        return $this->name;
    }

    public function getGender(): ?string
    {
        $this->prepare();

        return $this->gender;
    }

    public function getIsGraduated(): ?bool
    {
        $this->prepare();

        return $this->isGraduated;
    }
}
