<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? '' ?></title>
<base href="<?= $this->base('/') ?>" />
<link href="<?= $this->asset('layout.css') ?>" rel="stylesheet" />
<link href="<?= $this->asset('default.css') ?>" rel="stylesheet" />
<script src="<?= $this->asset('default.js') ?>"></script>
</head>
<body>

<div id="app">
    <header>
        <?php $this->insert('components/user_panel.php') ?>
        <?php $this->insert('components/nav.html') ?>
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