<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? '' ?></title>
<base href="<?= $this->base('/') ?>" />
<link href="<?= $this->base($this->asset('/layout.css')) ?>" rel="stylesheet" />
<link href="<?= $this->base($this->asset('/default.css')) ?>" rel="stylesheet" />
</head>
<body>

<div id="app">
    <header>
        <?php $this->insert('components/user_panel') ?>
        <div id="menu_mobile">
            <label>
                <input type="checkbox" />
            </label>
        </div>
        <?php $this->insert('components/nav') ?>
        <div>
            <form action="movies" id="search">
                <input name="q" placeholder="Pesquisar por título, diretor ou ano de lançamento" required="required" type="search" />
                <button type="submit">Pesquisar</button>
            </form>
        </div>
    </header>
    <section>
        <?= $this->section('content') ?>
    </section>
    <footer class="flex_column hcenter vcenter">Projeto em desenvolvimento</footer>
</div>

</body>
</html>