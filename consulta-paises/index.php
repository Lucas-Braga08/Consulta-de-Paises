<?php
declare(strict_types=1);
require __DIR__ . '/src/RestCountries.php';

$termo      = trim($_GET['pais'] ?? '');
$consultou  = isset($_GET['pais']);
$paises     = [];
$erro       = null;

if ($consultou) {
    if ($termo === '' || mb_strlen($termo) < 2 || mb_strlen($termo) > 60
        || !preg_match("/^[\p{L}\s.'\-]+$/u", $termo)) {
        $erro = 'Informe o nome de um país com 2 a 60 letras (ex.: Brasil, Japan, Alemanha).';
    } else {
        try {
            $paises = array_map('formatarPais', array_slice(buscarPaises($termo), 0, 12));
            if (!$paises) {
                $erro = 'Nenhum país encontrado para "' . $termo . '". Tente outro nome.';
            }
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta de Países — REST Countries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<main class="container py-5">

    <header class="text-center mb-4">
        <h1 class="fw-bold">🌍 Consulta de Países</h1>
        <p class="text-muted mb-0">Pesquise um país e veja capital, população, moeda e região — dados da API REST Countries.</p>
    </header>

    <!-- Formulário de pesquisa -->
    <form method="get" action="index.php" class="row g-2 justify-content-center mb-4">
        <div class="col-12 col-md-7 col-lg-6">
            <label for="pais" class="visually-hidden">Nome do país</label>
            <input type="text" id="pais" name="pais" class="form-control form-control-lg"
                   placeholder="Ex.: Brasil, Japan, Alemanha" value="<?= e($termo) ?>" required autofocus>
        </div>
        <div class="col-12 col-md-auto d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Consultar</button>
        </div>
    </form>

    <!-- Área de resultados -->
    <section id="resultados" aria-live="polite">
        <?php if ($erro): ?>
            <div class="alert alert-warning text-center"><?= e($erro) ?></div>
        <?php endif; ?>

        <div class="row g-4 justify-content-center">
            <?php foreach ($paises as $p): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm">
                        <?php if ($p['bandeira']): ?>
                            <img src="<?= e($p['bandeira']) ?>" class="card-img-top bandeira" alt="<?= e($p['bandeira_alt']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="h5 card-title mb-0"><?= e($p['nome']) ?> <small class="text-muted"><?= e($p['sigla']) ?></small></h2>
                            <p class="small text-muted"><?= e($p['nome_en']) ?></p>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Capital:</strong> <?= e($p['capital']) ?></li>
                                <li><strong>População:</strong> <?= e($p['populacao']) ?> hab.</li>
                                <li><strong>Moeda:</strong> <?= e($p['moeda']) ?></li>
                                <li><strong>Região:</strong> <?= e($p['regiao']) ?></li>
                                <li><strong>Sub-região:</strong> <?= e($p['subregiao']) ?></li>
                                <li><strong>Idiomas:</strong> <?= e($p['idiomas']) ?></li>
                            </ul>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="text-center text-muted small mt-5">
        Dados fornecidos por <a href="https://restcountries.com" target="_blank" rel="noopener">REST Countries</a>
    </footer>
</main>
</body>
</html>
