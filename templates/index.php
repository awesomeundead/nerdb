<div id="home_loggedin">
    <?php if ($movies): ?>
    <div class="movies flex_row">
        <div class="label"><?= $movies_label ?></div>
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
    <?php endif ?>

    <?php if ($games): ?>
    <div class="games flex_row">
        <div class="label"><?= $games_label ?></div>
        <div class="container">
        <?php foreach ($games as $item): ?>
            <div class="item flex_row">
            <div class="image">
                <a href="game/<?= $item['id'] ?>/<?= $item['title_url'] ?>">
                    <?php if ($item['media']): ?>
                    <img alt="<?= $item['title'] ?>" src="images/games/256/<?= $item['media'] ?>.webp" />
                    <?php else: ?>
                    <img alt="" src="noimage.png" />
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
    <?php endif ?>
</div>