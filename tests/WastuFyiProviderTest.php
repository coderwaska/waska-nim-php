<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wastukancana\Provider\WastuFyi;

final class WastuFyiProviderTest extends TestCase
{
    private const BASE_URI = 'https://api.wastu.fyi';

    private function makeClient(MockHandler $mock, string $token): Client
    {
        $handler = HandlerStack::create($mock);

        return new Client([
            'handler' => $handler,
            'base_uri' => self::BASE_URI,
            'verify' => false,
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);
    }

    private function json(array $payload): string
    {
        return json_encode($payload);
    }

    private function dataPayload(int $studentId, string $name, string $gender, ?string $status = null): array
    {
        $data = [
            'student_id' => $studentId,
            'name' => $name,
            'gender' => $gender,
        ];

        if ($status !== null) {
            $data['status'] = $status;
        }

        return ['data' => $data];
    }

    /**
     * @dataProvider statusProvider
     */
    public function test_status_parsing(string $status, bool $expectedGraduated): void
    {
        $payload = $this->dataPayload(211351143, 'SULUH SULISTIAWAN', 'M', $status);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $this->json($payload)),
        ]);

        $client = $this->makeClient($mock, 'token');
        $svc = new WastuFyi('211351143', 'token', $client);

        $this->assertSame('SULUH SULISTIAWAN', $svc->getName());
        $this->assertSame('M', $svc->getGender());
        $this->assertSame($expectedGraduated, $svc->getIsGraduated());
    }

    public function statusProvider(): array
    {
        return [
            'graduate' => ['LULUS', true],
            'non-graduate' => ['AKTIF', false],
        ];
    }

    public function test_missing_data_yields_nulls(): void
    {
        $payload = [
            'statusCode' => 200,
            'code' => 'OK',
        ];

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $this->json($payload)),
        ]);

        $client = $this->makeClient($mock, 'token');
        $svc = new WastuFyi('211351143', 'token', $client);

        $this->assertNull($svc->getName());
        $this->assertNull($svc->getGender());
        $this->assertNull($svc->getIsGraduated());
    }

    public function test_request_error_yields_nulls(): void
    {
        $mock = new MockHandler([
            function () {
                throw new RequestException(
                    'network failure',
                    new Request('GET', self::BASE_URI.'/students/detail?student_id=211351143')
                );
            },
        ]);

        $client = $this->makeClient($mock, 'token');
        $svc = new WastuFyi('211351143', 'token', $client);

        $this->assertNull($svc->getName());
        $this->assertNull($svc->getGender());
        $this->assertNull($svc->getIsGraduated());
    }
}
