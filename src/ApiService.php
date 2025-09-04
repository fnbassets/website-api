<?php

namespace Fnbassets\WebsiteApi;

use Fnbassets\WebsiteApi\Traits\HttpTrait;

class ApiService
{
    private $apiUrl;
    private $apiClient;
    private $apiKey;

    use HttpTrait;

    public function __construct($apiUrl = null, $apiClient = null, $apiKey = null)
    {
        $this->apiUrl = $apiUrl;
        $this->apiClient = $apiClient;
        $this->apiKey = $apiKey;
    }

    private function parseParams($options, $page, $limit)
    {
        $defaultParams = [
            'page' => $page,
            'limit' => $limit,
        ];

        // Combina os parâmetros padrão com os fornecidos em $options
        $params = array_merge($defaultParams, $options);

        // Converte os parâmetros para a string de query
        return http_build_query($params);
    }

    public function listLeiloes($options = [], $page = 1, $limit = 100)
    {

        $queryString = $this->parseParams($options, $page, $limit);
        return $this->callApi('get', '/api/public/leiloes?' . $queryString);
    }

    public function loadLeilao($id)
    {
        return $this->callApi('get', '/api/public/leiloes/' . $id);
    }

    public function listLotes($leilaoId = null, $options = [], $page = 1, $limit = 100)
    {
        if (!empty($leilaoId)) {
            $options['leilao'] = $leilaoId;
        }
        $queryString = $this->parseParams($options, $page, $limit);
        return $this->callApi('get', '/api/public/lotes?' . $queryString);
    }

    public function loadLote($id)
    {
        return $this->callApi('get', '/api/public/lotes/' . $id);
    }

    public function assistenteBuscadorLote($options, $leilao = null)
    {
        /*$options = [
            'criterio' => $criterio,
            'valor' => $valor,
        ];*/

        if (!empty($leilao)) {
            $options['leilao'] = $leilao;
        }
        $queryString = $this->parseParams($options, 1, 100);
        return $this->callApi('get', '/api/public/assistente/buscadorLote?' . $queryString);
    }

    public function listBens($options = [], $page = 1, $limit = 100)
    {
        if (!empty($leilaoId)) {
            $options['leilao'] = $leilaoId;
        }
        $queryString = $this->parseParams($options, $page, $limit);
        return $this->callApi('get', '/api/public/estoque?' . $queryString);
    }

    public function loadBem($id)
    {
        return $this->callApi('get', '/api/public/estoque/' . $id);
    }

    public function listBanners($options = [])
    {
        $queryString = $this->parseParams($options, 1, 10000);
        return $this->callApi('get', '/api/public/banners?' . $queryString);
    }

    public function listContents($options = [], $page = 1, $limit = 100)
    {
        $queryString = $this->parseParams($options, $page, $limit);
        return $this->callApi('get', '/api/public/contents?' . $queryString);
    }

    public function loadContent($id)
    {
        return $this->callApi('get', '/api/public/contents/' . $id);
    }

    public function loadContentByName($pageName)
    {
        return $this->callApi('get', '/api/public/contents/pageName/' . $pageName);
    }

    public function loadContentByUrl($url)
    {
        return $this->callApi('get', '/api/public/contents/pageUrl/' . $url);
    }

    public function listPosts($options = [], $page = 1, $limit = 100)
    {
        $queryString = $this->parseParams($options, $page, $limit);
        return $this->callApi('get', '/api/public/blog?' . $queryString);
    }

    public function loadPost($id)
    {
        return $this->callApi('get', '/api/public/blog/' . $id);
    }

    public function listMenus($options = [])
    {
        $queryString = $this->parseParams($options, 1, 10000);
        return $this->callApi('get', '/api/public/menus?' . $queryString);
    }

    public function getCacheVendedores()
    {
        return $this->callApi('get', '/api/public/cache/vendedores');
    }

    public function getComitente($id, $incluirEventos = true, $incluirDestaques = true)
    {
        $queryString = $this->parseParams([
            'incluirEventos' => $incluirEventos,
            'incluirDestaques' => $incluirDestaques,
        ], 1, 10000);
        return $this->callApi('get', '/api/public/comitentes/' . $id . '?' . $queryString);
    }

    public function login($username, $password, $headers = [])
    {
        return $this->callApi('post', '/api/auth', [
            'json' => [
                'user' => $username,
                'pass' => $password,
            ],
            'headers' => $headers
        ]);
    }

    /**
     * Métodos do usuário logado
     */
    public function recuperarSenha($userNameOrEmail)
    {
        return $this->callApi('post', '/api/public/arrematantes/service/recupera-senha', [
            'json' => [
                'login' => $userNameOrEmail
            ]
        ]);
    }

    /**
     * Métodos do usuário logado
     */
    public function recuperarSenhaConfirmar($id, $token, $password)
    {
        return $this->callApi('put', '/api/public/arrematantes/service/recupera-senha', [
            'json' =>  [
                'id' => $id,
                'token' => $token,
                'password' => $password
            ]
        ]);
    }

    public function userCredentials()
    {
        return $this->callAuthApi('get', '/api/userCredentials');
    }

    public function getLeiloesLotesFavoritos()
    {
        return $this->callAuthApi('get', '/api/arrematantes/meusFavoritos');
    }

    public function getLotesFavoritos($leilao = null)
    {
        $url = '/api/arrematantes/lotes/favoritos';
        if (!empty($leilao)) {
            $url .= '?leilao=' . $leilao;
        }
        return $this->callAuthApi('get', $url);
    }

    public function definirLoteFavorito($id)
    {
        return $this->callAuthApi('post', sprintf('/api/arrematantes/lotes/%s/favorito', $id));
    }

    public function removerLoteFavorito($id)
    {
        return $this->callAuthApi('delete', sprintf('/api/arrematantes/lotes/%s/favorito', $id));
    }

    public function definirLeilaoFavorito($id)
    {
        return $this->callAuthApi('post', sprintf('/api/arrematantes/leiloes/%s/favorito', $id));
    }

    public function getLeiloesFavoritos()
    {
        return $this->callAuthApi('get', '/api/arrematantes/leiloes/favoritos');
    }

    public function removerLeilaoFavorito($id)
    {
        return $this->callAuthApi('delete', sprintf('/api/arrematantes/leiloes/%s/favorito', $id));
    }

    public function definirBemFavorito($id)
    {
        return $this->callAuthApi('post', sprintf('/api/arrematantes/bens/%s/favorito', $id));
    }

    public function removerBemFavorito($id)
    {
        return $this->callAuthApi('delete', sprintf('/api/arrematantes/bens/%s/favorito', $id));
    }

    public function getHabilitacao($leilao)
    {
        return $this->callAuthApi('get', sprintf('/api/public/arrematantes/service/leiloes/%s/habilitar', $leilao));
    }

    public function habilitarLeilao($leilao, $lote = null, $data = [])
    {
        return $this->callAuthApi('post', sprintf('/api/public/arrematantes/service/leiloes/%s/habilitar?lote=%s', $leilao, $lote), [
            'json' => $data
        ]);
    }

    public function lance($loteId, $valor, $parcelado = false, $parcelas = null, $entrada = null)
    {
    }

    public function registrarContato($assunto, $mensagem, $tipoId = null, $personId = null, $email = null, $telefone = null, $extra = [])
    {
    }

    public function enviarProposta($bem, $proposta)
    {
        return $this->callAuthApi('POST', '/api/public/arrematantes/service/bem/' . $bem . '/enviar-proposta', [
            'json' => $proposta
        ]);
    }

    public function getFiltros(array $query = [])
    {
        return $this->callApi('get', '/api/public/leiloes/filtros', [
            'query' => $query
        ]);
    }

    public function mapa(array $query = [])
    {
        return $this->callApi('get', '/api/public/services/stockMap', [
            'query' => $query
        ]);
    }

    public function newsletterCreate(array $data = [])
    {
        return $this->callApi('post', '/api/public/newsletter', [
            'json' => $data
        ]);
    }

    public function getSetoresAtendimento(array $data = [])
    {
        return $this->callApi('get', '/api/public/atendimento/setores', [
            'query' => $data
        ]);
    }

    public function criarAtendimento(array $data = [])
    {
        return $this->callApi('post', '/api/public/atendimento', [
            'json' => $data
        ]);
    }

    public function getComitentes(array $data = [])
    {
        return $this->callApi('get', '/api/public/comitentes', [
            'query' => $data
        ]);
    }

    public function consultaNotaArrematacao($numero)
    {
        return $this->callApi('GET', '/api/public/services/consultaNota/' . $numero);
    }
}