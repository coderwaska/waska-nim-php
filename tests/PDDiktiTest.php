<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wastukancana\PDDikti;

final class PDDiktiTest extends TestCase
{
    private const BASE_URI = 'https://api-pddikti.kemdiktisaintek.go.id';

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
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['mahasiswa' => []])),
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
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'mahasiswa' => [(object) ['sinkatan_pt' => 'OTHER PT']],
            ])),
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
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'mahasiswa' => [(object) ['sinkatan_pt' => 'STT WASTUKANCANA', 'id' => 'ID123', 'nama' => 'Foo']],
            ])),
            new Response(204),
            function () {
                throw new RequestException('network failure', new Request('GET', self::BASE_URI.'/detail/mhs/ID123'));
            },
        ]);

        $client = $this->makeClientFromMock($mock);
        $svc = new PDDikti('211351143');
        $this->injectClient($svc, $client);

        $this->assertNull($svc->getGender());
        $this->assertNull($svc->getIsGraduated());
    }
}
