<?php

use PHPUnit\Framework\TestCase;
use Fnbassets\WebsiteApi\ApiService;

class ApiClientTest extends TestCase
{

    static $apiService;
    static $username = null;
    static $password = null;
    static $tokenLogged = null;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        // Carrega o .env.test
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env.test');
        $dotenv->load();
        self::$username = $_ENV['TEST_USER_USERNAME'];
        self::$password = $_ENV['TEST_USER_PASSWORD'];

        if (empty(self::$apiService)) {
            self::$apiService = new ApiService($_ENV['API_BASE_URL'], $_ENV['API_CLIENT'], $_ENV['API_KEY']);
        }
        parent::__construct($name, $data, $dataName);
    }

    public function client()
    {
        return self::$apiService;
    }

    public function testSecurityApi()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Falha na autenticação');

        $apiFake = new ApiService($_ENV['API_BASE_URL'], $_ENV['API_CLIENT'], 'InvalidToken:P');
        $apiFake->loadLote(1);
    }

    public function testClientInstance()
    {
        $this->assertInstanceOf(ApiService::class, $this->client());
    }

    public function testListLeiloes()
    {
        $leiloes = $this->client()->listLeiloes([], 1, 100);
        $this->assertIsArray($leiloes);
        $this->assertArrayHasKey('result', $leiloes);
        $this->assertArrayHasKey('total', $leiloes);
        echo "\r\n";
        print_r("!! Cada item de resultado de leilão tem aproximadamente " . round(strlen(json_encode($leiloes['result'][0]))/ 1024) . ' KB');
        echo "\r\n";
    }

    // @TODO: Testar todos os filtros possíveis de leilão

    public function testListLeiloesVendaDireta()
    {
        $leiloes = $this->client()->listLeiloes(['vendaDireta' => 1], 1, 100);
        $this->assertIsArray($leiloes);
        $this->assertArrayHasKey('result', $leiloes);
        $this->assertArrayHasKey('total', $leiloes);
        $this->assertEquals($leiloes['result'][0]['vendaDireta'], true);
    }

    public function testListLeiloesPaginacao()
    {
        $leiloes = $this->client()->listLeiloes([], 1, 1);
        $this->assertIsArray($leiloes);
        $this->assertEquals(count($leiloes['result']), 1);
        $leiloes = $this->client()->listLeiloes([], 1, 2);
        $this->assertIsArray($leiloes);
        $this->assertEquals(count($leiloes['result']), 2);
    }

    public function testLoadLeilao()
    {
        $lote = $this->client()->loadLeilao(1);
        $this->assertIsArray($lote);
        $this->assertArrayHasKey('id', $lote);
        $this->assertEquals($lote['id'], 1);
    }

    public function testListLotes()
    {
        $lotes = $this->client()->listLotes(1, [], 1, 100);
        $this->assertIsArray($lotes);
        $this->assertArrayHasKey('result', $lotes);
        $this->assertArrayHasKey('total', $lotes);
        $totalComPaginacao = $lotes['total'];
        foreach($lotes['result'] as $lote) {
            $this->assertEquals($lote['leilao']['id'], 1);
        }
        echo "\r\n";
        print_r("!! Cada item de resultado de lote tem aproximadamente " . round(strlen(json_encode($lotes['result'][0]))/ 1024) . ' KB');
        echo "\r\n";

        // Testando se lista lotes com leilão ID 2
        $lotes = $this->client()->listLotes(2, [], 1, 100);
        $this->assertIsArray($lotes);
        $this->assertArrayHasKey('result', $lotes);
        $this->assertArrayHasKey('total', $lotes);
        foreach($lotes['result'] as $lote) {
            $this->assertEquals($lote['leilao']['id'], 2);
        }

        // Testando lista completa de lotes
        $lotes = $this->client()->listLotes(null, [], 1, 100);
        $this->assertGreaterThan($totalComPaginacao, $lotes['total']);
    }

    // @TODO: Testar todos os filtros possíveis de lote

    public function testListLotesPaginacao()
    {
        $lotes = $this->client()->listLotes(null, [], 1, 1);
        $this->assertIsArray($lotes);
        $this->assertEquals(count($lotes['result']), 1);
        $lotes = $this->client()->listLotes(null, [], 1, 2);
        $this->assertIsArray($lotes);
        $this->assertEquals(count($lotes['result']), 2);
    }

    public function testLoadLote()
    {
        $lote = $this->client()->loadLote(1);
        $this->assertIsArray($lote);
        $this->assertArrayHasKey('id', $lote);
        $this->assertEquals($lote['id'], 1);
    }

    public function testListBens()
    {
        $bens = $this->client()->listBens([], 1, 100);
        $this->assertIsArray($bens);
        $this->assertArrayHasKey('result', $bens);
        $this->assertArrayHasKey('total', $bens);
        echo "\r\n";
        print_r("!! Cada item de resultado de bem tem aproximadamente " . round(strlen(json_encode($bens['result'][0]))/ 1024) . ' KB');
        echo "\r\n";
    }

    // @TODO: Testar todos os filtros possíveis de bem

    public function testLoadBem()
    {
        $bem = $this->client()->loadBem(1);
        $this->assertIsArray($bem);
        $this->assertArrayHasKey('id', $bem);
        $this->assertEquals($bem['id'], 1);
    }

    public function testListBanners()
    {
        $banners = $this->client()->listBanners([
            'sortBy' => 'order',
            'descending' => false
        ]);
        $this->assertIsArray($banners);
        $this->assertArrayHasKey('result', $banners);
        $this->assertArrayHasKey('total', $banners);
    }

    public function testListPages()
    {
        $contents = $this->client()->listContents([
            'sortBy' => 'order',
            'descending' => false
        ]);
        $this->assertIsArray($contents);
        $this->assertArrayHasKey('result', $contents);
        $this->assertArrayHasKey('total', $contents);
    }

    public function testLoadContent()
    {
        $content = $this->client()->loadContent(1);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('id', $content);
        $this->assertEquals($content['id'], 1);
    }

    public function testListMenus()
    {
        $contents = $this->client()->listMenus([
            'sortBy' => 'order',
            'descending' => false
        ]);
        $this->assertIsArray($contents);
        $this->assertArrayHasKey('result', $contents);
        $this->assertArrayHasKey('total', $contents);
    }

    public function testRecuperarSenha()
    {
        $recuperarSenha = $this->client()->recuperarSenha(self::$username);
        $this->assertIsArray($recuperarSenha);
        $this->assertArrayHasKey('status', $recuperarSenha);
        $this->assertEquals('OK', $recuperarSenha['status']);
    }

    public function testLogin()
    {
        $userData = $this->client()->login(self::$username, self::$password, ['uloc-mi' => $_ENV['API_CLIENT']]);
        $this->assertArrayHasKey('token', $userData);
        $token = $userData['token'];
    }

}
