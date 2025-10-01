<div id="game">
    <div class="grid">
        <div class="image flex_row">
            <?php if ($game['media']): ?>
            <img alt="" src="images/games/512/<?= $game['media'] ?>.webp" />
            <?php else: ?>
            <img alt="" src="noimage.png" />
            <?php endif ?>
        </div>
        <div class="flex_row">
            <div>
                <h1 class="title"><?= $game['title'] ?></h1>
            </div>
            <div class="listing">
                <div>Gêneros:</div>
                <div class="genres">
                <?php foreach ($game['genres'] as $genre): ?>
                    <a href="games/search?q=genero:<?= $genre ?>"><?= $genre ?></a>
                <?php endforeach ?>
                </div>
            </div>
            <div class="listing">
                <div>Ano de lançamento</div>
                <div class="release_year"><?= $game['release_year'] ?></div>
            </div>
            <div class="listing">
                <div>Desenvolvedor</div>
                <div class="developer">
                <?php foreach ($game['developer'] as $developer): ?>
                    <a href="games/search?q=desenvolvedor:<?= $developer ?>"><?= $developer ?></a>
                <?php endforeach ?>
                </div>
            </div>
            <div class="listing services">
                <div>Serviços</div>
                <div class="container">
                <?php if ($game['steam']): ?>
                    <a class="steam" href="https://steampowered.com./app/<?= $game['steam'] ?>/" target="_blank">
                        <img src="icon_steam_logo.png" />
                    </a>
                <?php endif ?>
                </div>
            </div>

            <?php if ($session->logged_in ?? false): ?>
            <div class="toggle flex_column">
                <button aria-label="Quero jogar" class="icon listed" data-action="listed" title="Quero jogar" type="button"></button>
                <button aria-label="Já joguei" class="icon completed" data-action="completed" title="Já joguei" type="button"></button>
                <button aria-label="Gostei" class="icon like" data-action="liked" title="Gostei" type="button"></button>
            </div>
            <div>Minha avaliação</div>
            <div class="rating flex_column">
                <button class="star" data-value="1" title="1 estrela" type="button"></button>
                <button class="star" data-value="2" title="2 estrelas" type="button"></button>
                <button class="star" data-value="3" title="3 estrelas" type="button"></button>
                <button class="star" data-value="4" title="4 estrelas" type="button"></button>
                <button class="star" data-value="5" title="5 estrelas" type="button"></button>
                <button class="star" data-value="6" title="6 estrelas" type="button"></button>
                <button class="star" data-value="7" title="7 estrelas" type="button"></button>
                <button class="star" data-value="8" title="8 estrelas" type="button"></button>
                <button class="star" data-value="9" title="9 estrelas" type="button"></button>
                <button class="star" data-value="10" title="10 estrelas" type="button"></button>
            </div>
            <script>

                const game = {
                    listed: <?= $game['listed'] ?? 0 ?>,
                    completed: <?= $game['completed'] ?? 0 ?>,
                    liked: <?= $game['liked'] ?? 0 ?>,
                    rating: <?= $game['rating'] ?? 0 ?>,
                };

                document.querySelectorAll('.toggle button').forEach(item =>
                {
                    if (game[item.dataset.action])
                    {            
                        item.classList.add('selected');
                        item.dataset.value = 0;
                    }
                    else
                    {
                        item.dataset.value = 1;
                    }

                    item.addEventListener('click', () => 
                    {
                        reaction(item);
                    });
                });

                document.querySelectorAll('.rating button').forEach(item =>
                {
                    if (game.rating >= item.dataset.value)
                    {
                        item.classList.add('starred');
                    }

                    item.addEventListener('click', () =>
                    {
                        const value = (game.rating == item.dataset.value) ? 0 : item.dataset.value;
                        game.rating = value;

                        rating(value);
                    });
                });

            </script>
            <?php else: ?>
            <div class="add">
                <a href="login">
                    <img alt="Ícone de salvar na lista" height="32px" src="listed_empty.png" />
                    <span>Adicionar à minha lista</span>
                </a>
            </div>
            <?php endif ?>
        </div>
    </div>

    <?php if ($game['friends']): ?>
    <div class="friends flex_row">
        <div class="label">Avaliação dos amigos</div>
        <div class="container">
        <?php foreach ($game['friends'] as $friend): ?>
            <div class="item">
                <div class="flex_column">
                    <div class="flex_row">
                        <div class="image">
                            <a href="friend/<?= $friend['user_id'] ?>/gamelist">
                                <img alt="" src="https://avatars.steamstatic.com/<?= $friend['avatarhash'] ?>_full.jpg" />
                            </a>
                        </div>
                        <div class="personaname"><?= $friend['personaname'] ?></div>
                    </div>
                    <div class="flex_row">
                        <div class="flex_column">
                            <?php if ($friend['listed']): ?>
                            <div aria-label="Quer jogar" class="icon listed friend" title="Quer jogar"></div>
                            <?php else: ?>
                            <div class="icon listed friend disable"></div>
                            <?php endif ?>
                        </div>

                        <div class="flex_column">
                            <?php if ($friend['completed']): ?>
                            <div aria-label="Já jogou" class="icon completed friend" title="Já jogou"></div>
                            <?php else: ?>
                            <div class="icon completed friend disable"></div>
                            <?php endif ?>
                        </div>

                        <div class="flex_column">
                            <?php if ($friend['liked']): ?>
                            <div aria-label="Gostou" class="icon liked friend" title="Gostou"></div>
                            <?php else: ?>
                            <div class="icon liked friend disable"></div>
                            <?php endif ?>
                        </div>

                        <div class="flex_column">
                            <?php if ($friend['rating']): ?>
                            <div aria-label="Avaliação" class="icon rating friend" title="Avaliação"><?= $friend['rating'] ?></div>
                            <?php else: ?>
                            <div class="icon rating friend disable"></div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
        </div>
    </div>
    <?php endif ?>

    <?php if ($session->logged_in ?? false): ?>
        <div class="update">
            <a href="game/update/<?= $game['id'] ?>">Editar jogo</a>
        </div>
    <?php endif ?>

    <?php if ($game['related_games']): ?>
    <div class="related flex_row">
        <div class="label">Jogos relacionados ou semelhantes</div>
        <div class="container">
        <?php foreach ($game['related_games'] as $item): ?>
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

<script>

const game_id = '<?= $game['id'] ?>';

function reaction(button)
{
    const action = button.dataset.action;
    const value = button.dataset.value;

    requestJSON(`api/v1/mylist/game/${game_id}`, 'post',
    {
        [action]: value
    })
    .then(json =>
    {
        if (json.status == 'success')
        {
            button.classList.toggle('selected');
            button.dataset.value = value == 1 ? 0 : 1;
        }
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function rating(value)
{
    requestJSON(`api/v1/mylist/game/${game_id}`, 'post',
    {
        rating: value
    })
    .then(json =>
    {
        if (json.status == 'success')
        {
            document.querySelectorAll('.rating button').forEach(item =>
            {
                item.classList.remove('starred');

                if (value)
                {
                    if (parseInt(value) >= parseInt(item.dataset.value))
                    {
                        item.classList.add('starred');
                    }
                }
            });
        }
    })
    .catch(error =>
    {
        console.error(error);
    });
}

</script>