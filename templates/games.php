<div id="best-rated">
    <h1>Melhores jogos avaliados por usuários do site</h1>
    <div class="container">
    <?php foreach ($games as $item): ?>
        <div class="item flex_row">
            <div class="image">
                <a href="game/<?= $item['id'] ?>/<?= $item['title_url'] ?>">
                    <?php if ($item['media']): ?>
                    <img alt="<?= $item['title'] ?>" src="images/games/256/<?= $item['media'] ?>.webp" />
                    <?php else: ?>
                    <img alt="Sem imagem" src="noimage.png" />
                    <?php endif ?>
                </a>
            </div>
            <div>
                <div class="title"><?= $item['title'] ?> (<?= $item['release_year'] ?>)</div>
            </div>
        </div>
    <?php endforeach ?>
    </div>
</div>