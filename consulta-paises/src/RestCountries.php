<?php
declare(strict_types=1);

const API_BASE          = 'https://api.restcountries.com/countries/v5';
const LIMITE_RESULTADOS = 12;

/**
 * Lê a chave da API: config.php (recomendado) ou variável de ambiente RESTCOUNTRIES_API_KEY.
 */
function carregarChave(): string
{
    $arquivo = __DIR__ . '/../config.php';
    $config  = is_file($arquivo) ? require $arquivo : [];
    $chave   = $config['api_key'] ?? (getenv('RESTCOUNTRIES_API_KEY') ?: '');

    return trim((string) $chave);
}

/**
 * Faz uma requisição GET (cURL) autenticada e devolve status HTTP + JSON decodificado.
 */
function requisitar(string $url, string $chave): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Authorization: Bearer ' . $chave],
    ]);

    $corpo  = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($corpo === false) {
        return ['status' => 0, 'dados' => null];
    }

    return ['status' => $status, 'dados' => json_decode($corpo, true)];
}

/**
 * Busca países pelo nome. Tenta primeiro a busca por nomes (/name) e, se não achar,
 * a busca de texto livre (?q=). Retorna lista de países (vazia se nada foi encontrado)
 * ou lança RuntimeException com uma mensagem amigável em caso de falha.
 */
function buscarPaises(string $termo): array
{
    $chave = carregarChave();
    if ($chave === '' || $chave === 'COLE_SUA_CHAVE_AQUI') {
        throw new RuntimeException('Chave da API não configurada. Copie config.example.php para config.php e cole sua chave (veja o README).');
    }

    $params = http_build_query(['q' => $termo, 'limit' => LIMITE_RESULTADOS]);
    $rotas  = [API_BASE . '/name?' . $params, API_BASE . '?' . $params];

    foreach ($rotas as $url) {
        $r = requisitar($url, $chave);

        switch ($r['status']) {
            case 0:
                throw new RuntimeException('Não foi possível conectar à API. Verifique sua internet e tente novamente.');
            case 200:
                $lista = $r['dados']['data']['objects'] ?? null;
                if (is_array($lista) && $lista) {
                    return array_values(array_filter($lista, 'is_array'));
                }
                break; // 200 sem resultados: tenta a próxima rota
            case 404:
                break; // sem resultados: tenta a próxima rota
            case 401:
                throw new RuntimeException('Chave da API inválida ou ausente. Confira o arquivo config.php.');
            case 403:
                throw new RuntimeException('Acesso negado pela API (limite mensal do plano gratuito atingido ou conta inativa).');
            case 429:
                throw new RuntimeException('Muitas requisições em pouco tempo. Aguarde alguns segundos e tente de novo.');
            default:
                throw new RuntimeException("A API respondeu com erro (HTTP {$r['status']}). Tente novamente mais tarde.");
        }
    }

    return [];
}

/**
 * Converte o JSON bruto da API (v5) em um array simples, pronto para exibir.
 */
function formatarPais(array $p): array
{
    $moedas = [];
    foreach ($p['currencies'] ?? [] as $m) {
        $moedas[] = ($m['name'] ?? $m['code'] ?? '?') . ' (' . ($m['code'] ?? '') . (!empty($m['symbol']) ? ' · ' . $m['symbol'] : '') . ')';
    }

    $capitais = array_filter(array_column($p['capitals'] ?? [], 'name'));
    $idiomas  = array_filter(array_column($p['languages'] ?? [], 'name'));

    return [
        'nome'         => $p['names']['translations']['por']['common'] ?? $p['names']['common'] ?? '—',
        'nome_en'      => $p['names']['official'] ?? '—',
        'capital'      => $capitais ? implode(', ', $capitais) : '—',
        'populacao'    => number_format((int) ($p['population'] ?? 0), 0, ',', '.'),
        'moeda'        => $moedas ? implode(', ', $moedas) : '—',
        'regiao'       => $p['region'] ?? '—',
        'subregiao'    => $p['subregion'] ?? '—',
        'idiomas'      => $idiomas ? implode(', ', $idiomas) : '—',
        'bandeira'     => $p['flag']['url_png'] ?? null,
        'bandeira_alt' => $p['flag']['description'] ?? 'Bandeira',
        'sigla'        => $p['codes']['alpha_2'] ?? '',
    ];
}
