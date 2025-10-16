<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wastukancana\Provider\PDDikti;

final class PDDiktiProviderTest extends TestCase
{
    private const BASE_URI = 'https://api-pddikti.kemdiktisaintek.go.id';

    private function json(array $payload): string
    {
        return json_encode($payload);
    }

    private function makeListPayload(array $entries): array
    {
        return ['mahasiswa' => $entries];
    }

    private function listEntry(string $id, string $nama, string $pt): array
    {
        return [
            'id' => $id,
            'nama' => $nama,
            'sinkatan_pt' => $pt,
        ];
    }

    private function detailPayload(string $jk, string $status): array
    {
        return [
            'jenis_kelamin' => $jk,
            'status_saat_ini' => $status,
        ];
    }

    private function injectClient(PDDikti $service, Client $client): void
    {
        $ref = new ReflectionClass(PDDikti::class);
        $prop = $ref->getProperty('http');

        $prop->setAccessible(true);
        $prop->setValue($service, $client);
    }

    private function makeClientFromMock(MockHandler $mock): Client
    {
        $handler = HandlerStack::create($mock);

        return new Client([
            'handler' => $handler,
            'base_uri' => self::BASE_URI,
            'verify' => false,
        ]);
    }

    public function test_fetch_data_handles_exception_on_options(): void
    {
        $mock = new MockHandler([
            function () {
                throw new RequestException(
                    'network failure',
                    new Request('OPTIONS', self::BASE_URI.'/pencarian/all/211351143')
                );
            },
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertNull($svc->getName());
    }

    public function test_prepare_list_handles_empty_mahasiswa(): void
    {
        $mock = new MockHandler([
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json(['mahasiswa' => []])),
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertNull($svc->getName());
    }

    public function test_prepare_detail_handles_empty_id(): void
    {
        $mock = new MockHandler([
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json(
                $this->makeListPayload([
                    [
                        'sinkatan_pt' => 'OTHER PT',
                        'id' => 'q4DrGQZKnuBkRIfbBHwecTrbVMz7AEvAWnOuS-OPIrpF0gsI69ER-pgcueN8cTOtGoDnYA==',
                        'nama' => 'SULUH SULISTIAWAN',
                    ],
                ])
            )),
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertNull($svc->getGender());
    }

    public function test_prepare_detail_handles_empty_data(): void
    {
        $mock = new MockHandler([
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json(
                $this->makeListPayload([
                    [
                        'sinkatan_pt' => 'STT WASTUKANCANA',
                        'id' => 'q4DrGQZKnuBkRIfbBHwecTrbVMz7AEvAWnOuS-OPIrpF0gsI69ER-pgcueN8cTOtGoDnYA==',
                        'nama' => 'Fulan bin Fulan',
                    ],
                ])
            )),
            new Response(204),
            function () {
                throw new RequestException(
                    'network failure',
                    new Request('GET', self::BASE_URI.'/detail/mhs/q4DrGQZKnuBkRIfbBHwecTrbVMz7AEvAWnOuS-OPIrpF0gsI69ER-pgcueN8cTOtGoDnYA==')
                );
            },
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertNull($svc->getGender());
        $this->assertNull($svc->getIsGraduated());
    }

    /**
     * @dataProvider detailStatusProvider
     */
    public function test_parses_list_and_detail_with_status(string $status, bool $expectedGraduated): void
    {
        $list = $this->makeListPayload([
            $this->listEntry(
                'q4DrGQZKnuBkRIfbBHwecTrbVMz7AEvAWnOuS-OPIrpF0gsI69ER-pgcueN8cTOtGoDnYA==',
                'SHOULD NOT USE',
                'OTHER PT'
            ),
            $this->listEntry(
                'j2Cq-X_ujvdAjBkbZ1QcrrJFV15iggI6WYjYncDJ5CaVAgSoB6MKL7aCW_VrVrs_wHnYFA==',
                'SULUH SULISTIAWAN',
                'STT WASTUKANCANA'
            ),
        ]);

        $detail = $this->detailPayload('L', $status);

        $mock = new MockHandler([
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json($list)),
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json($detail)),
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertSame('SULUH SULISTIAWAN', $svc->getName());
        $this->assertSame('M', $svc->getGender());
        $this->assertSame($expectedGraduated, $svc->getIsGraduated());
    }

    public function detailStatusProvider(): array
    {
        return [
            'non-graduate status' => ['Aktif-2024/2025 Ganjil', false],
            'graduate substring status' => ['Lulus-2024/2025 Ganjil', true],
        ];
    }

    /**
     * @dataProvider genderStatusMatrixProvider
     */
    public function test_parses_detail_gender_and_status_matrix(string $jk, string $status, string $expectedGender, bool $expectedGraduated): void
    {
        $list = $this->makeListPayload([
            $this->listEntry(
                'j2Cq-X_ujvdAjBkbZ1QcrrJFV15iggI6WYjYncDJ5CaVAgSoB6MKL7aCW_VrVrs_wHnYFA==',
                'SULUH SULISTIAWAN',
                'STT WASTUKANCANA'
            ),
        ]);

        $detail = $this->detailPayload($jk, $status);

        $mock = new MockHandler([
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json($list)),
            new Response(204),
            new Response(200, ['Content-Type' => 'application/json'], $this->json($detail)),
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertSame($expectedGender, $svc->getGender());
        $this->assertSame($expectedGraduated, $svc->getIsGraduated());
    }

    public function genderStatusMatrixProvider(): array
    {
        return [
            'male non-graduate' => ['L', 'Aktif-2024/2025 Ganjil', 'M', false],
            'male graduate' => ['L', 'Lulus-2024/2025 Ganjil', 'M', true],
            'female non-graduate' => ['P', 'Aktif-2024/2025 Ganjil', 'F', false],
            'unknown gender treated as F' => ['?', 'Lulus-2024/2025 Ganjil', 'F', true],
        ];
    }
}
