<?php

namespace Wastukancana;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class PDDikti
{
    private Client $http;

    private string $nim;

    private ?string $id = null;

    private ?string $name = null;

    private ?string $gender = null;

    private ?bool $isGraduated = null;

    public function __construct(string $nim)
    {
        $this->http = new Client([
            'base_uri' => 'https://api-pddikti.kemdiktisaintek.go.id',
            'verify' => false,
        ]);
        $this->nim = ltrim($nim, '0');
    }

    private function fetchData(string $endpoint): ?object
    {
        $ip = long2ip(rand(0, 4294967295));

        try {
            $this->http->request('OPTIONS', $endpoint, [
                'headers' => [
                    'X-User-Ip' => $ip,
                    'Origin' => 'https://pddikti.kemdiktisaintek.go.id',
                    'Access-Control-Request-Method' => 'GET',
                    'Access-Control-Request-Headers' => 'X-User-Ip',
                ],
            ]);
        } catch (Exception $e) {
            return null;
        }

        try {
            $response = $this->http->request('GET', $endpoint, [
                'headers' => [
                    'X-User-Ip' => $ip,
                    'Origin' => 'https://pddikti.kemdiktisaintek.go.id',
                ],
            ]);

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

    private function prepareList(): void
    {
        if ($this->id !== null) {
            return;
        }

        $data = $this->fetchData("pencarian/all/$this->nim");

        if (! $data || empty($data->mahasiswa)) {
            return;
        }

        $filtered = array_filter(
            $data->mahasiswa,
            fn ($i) => isset($i->sinkatan_pt) && $i->sinkatan_pt === 'STT WASTUKANCANA'
        );

        $selected = ! empty($filtered) ? reset($filtered) : null;

        $this->id = $selected->id ?? null;
        $this->name = $selected->nama ?? null;
    }

    private function prepareDetail(): void
    {
        if ($this->gender !== null) {
            return;
        }

        $this->prepareList();

        if (! $this->id) {
            return;
        }

        $data = $this->fetchData("detail/mhs/$this->id");

        if (! $data) {
            return;
        }

        $this->gender = $data->jenis_kelamin === 'L' ? 'M' : 'F';
        $this->isGraduated = stripos($data->status_saat_ini ?? '', 'lulus') !== false;
    }

    public function getName(): ?string
    {
        $this->prepareList();

        return $this->name;
    }

    public function getGender(): ?string
    {
        $this->prepareDetail();

        return $this->gender;
    }

    public function getIsGraduated(): ?bool
    {
        $this->prepareDetail();

        return $this->isGraduated;
    }
}
