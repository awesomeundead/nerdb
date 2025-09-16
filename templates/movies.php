<div id="best-rated">
    <h1>Melhores filmes avaliados por usuários do site</h1>
    <div class="container">
    <?php foreach ($movies as $item): ?>
        <div class="item flex_row">
            <div class="image">
                <a href="movie/<?= $item['id'] ?>/<?= $item['title_url'] ?>">
                    <?php if ($item['media']): ?>
                    <img alt="<?= $item['title_br'] ?>" src="images/256/<?= $item['media'] ?>.webp" />
                    <?php else: ?>
                    <img alt="" src="noimage.png" />
                    <?php endif ?>
                </a>
            </div>
            <div>
                <div class="title"><?= $item['title_br'] ?> (<?= $item['release_year'] ?>)</div>
            </div>
        </div>
    <?php endforeach ?>
    </div>
</div>