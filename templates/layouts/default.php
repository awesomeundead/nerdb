<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php foreach ($open_graph as $property => $content): ?>
<meta content="<?= $this->e($content) ?>"property="og:<?= $property ?>" />
<?php endforeach ?>
<title><?= empty($title) ? 'NERDB' : "{$title} | NERDB" ?></title>
<base href="<?= $this->base('/') ?>" />
<link href="<?= $this->asset('layout.css') ?>" rel="stylesheet" />
<link href="<?= $this->asset('default.css') ?>" rel="stylesheet" />
<link href="assets/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180" />
<link href="assets/favicon-32x32.png" rel="icon" type="image/png" sizes="32x32" />
<link href="assets/favicon-16x16.png" rel="icon" type="image/png" sizes="16x16" />
<link href="assets/site.webmanifest" rel="manifest" />
<script src="<?= $this->asset('default.js') ?>"></script>
</head>
<body>

<div id="app">
    <header class="main">
        <div class="user_panel">
            <?php if ($session->logged_in): ?>
            <?php $this->insert('components/connected.php') ?>
            <?php else: ?>
            <a class="steam_disconnected" href="" title="Entrar com a Steam">
                <span>Entrar com a</span>
                <img alt="Link para se conectar via Steam" src="logo_steam.svg" />
            </a>
            <script>

            document.querySelector('.steam_disconnected').href = `auth?redirect=${relativePath}`;

            </script>
            <?php endif ?>
        </div>
        <div class="flex_column">
            <div class="logo">
                <a href="">
                    <img alt="" src="nerdb_logo.png" />
                </a>
            </div>
            <nav class="main">
                <a href="movies">Filmes</a>
                <a href="games">Jogos</a>
            </nav>
        </div>
        <div>
            <form id="search">
                <select>
                    <option selected="selected" value="movies">Filmes</option>
                    <option value="games">Jogos</option>
                </select>
                <input name="q" required="required" type="search" />
                <button type="submit">Pesquisar</button>
            </form>
        </div>
    </header>
    <section>
        <?= $this->section('content') ?>
    </section>
    <footer class="flex_column hcenter vcenter">
        <a href="https://github.com/awesomeundead/projeto_abigo" target="_blank">Projeto em desenvolvimento</a>
    </footer>
</div>

<script>

function main_search()
{
    const search = document.querySelector('#search');
    const input = search.querySelector('input');
    const select = search.querySelector('select');
    const placeholder =
    {
        'games': 'Pesquisar por título, desenvolvedor ou ano de lançamento',
        'movies': 'Pesquisar por título, diretor ou ano de lançamento'
    };
    const update = () =>
    {
        search.action = `${select.value}/search`;
        input.placeholder = placeholder[select.value];
    };
    
    select.addEventListener('change', update);
    
    const pattern = /^game[s]?$/

    if (pattern.test(routeSegments[0]))
    {
        select.value = 'games';
    }

    update();
}

main_search();
    
</script>

</body>
</html>