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
            <form action="movies" id="search">
                <select>
                    <option data-placeholder="Pesquisar por título, diretor ou ano de lançamento" selected="selected" value="movies">Filmes</option>
                    <option data-placeholder="Pesquisar por título, desenvolvedor ou ano de lançamento" value="games">Jogos</option>
                </select>
                <input name="q" placeholder="Pesquisar por título, diretor ou ano de lançamento" required="required" type="search" />
                <button type="submit">Pesquisar</button>
            </form>
        </div>
<script>

const search = document.querySelector('#search');

search.querySelector('select').addEventListener('change', e =>
{
    const select = e.target;

    search.action = select.value;
    search.querySelector('[type="search"]').placeholder = select.querySelector(`[value="${select.value}"]`).dataset.placeholder;
});
    
</script>

    </header>
    <section>
        <?= $this->section('content') ?>
    </section>
    <footer class="flex_column hcenter vcenter">Projeto em desenvolvimento</footer>
</div>

</body>
</html>