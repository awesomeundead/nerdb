<div id="movie">
    <div class="grid">
        <div class="image flex_row">
            <?php if ($movie['media']): ?>
            <img alt="<?= $movie['title_br'] ?>" src="images/512/<?= $movie['media'] ?>.webp" />
            <?php else: ?>
            <img alt="" src="noimage.png" />
            <?php endif ?>
        </div>
        <div class="flex_row">
            <div>
                <h1 class="title_br"><?= $movie['title_br'] ?></h1>
            </div>
            <div class="listing">
                <div>Título em inglês</div>
                <div class="title_us"><?= $movie['title_us'] ?></div>
            </div>
            <div class="listing">
                <div>Gêneros:</div>
                <div class="genres flex_column">
                <?php foreach ($movie['genres'] as $genre): ?>
                    <a href="movies/search?q=genero:<?= $genre ?>"><?= $genre ?></a>
                <?php endforeach ?>
                </div>
            </div>
            <div class="listing">
                <div>Ano de lançamento</div>
                <div class="release_year"><?= $movie['release_year'] ?></div>
            </div>
            <div class="listing">
                <div>Diretor</div>
                <div class="director flex_row">
                <?php foreach ($movie['director'] as $director): ?>
                    <a href="movies/search?q=diretor:<?= $director ?>"><?= $director ?></a>
                <?php endforeach ?>
                </div>
            </div>
            <div class="listing services">
                <div>Serviços</div>
                <div class="container">
                    <a class="imdb" href="https://www.imdb.com/pt/title/<?= $movie['imdb'] ?>/" target="_blank">
                        <img alt="IMDB Logo" src="icon_imdb_logo.png" />
                    </a>
                    <?php foreach ($movie['platforms'] as $platform): ?>
                    <a href="<?= $platform['url'] ?>" target="_blank">
                        <img alt="<?= $platform['name'] ?> Logo" src="<?= $platform['icon'] ?>.png" />
                    </a>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if ($session->logged_in ?? false): ?>
            <div class="toggle flex_column">
                <button aria-label="Quero assistir" class="icon listed" data-action="listed" title="Quero assistir" type="button"></button>
                <button aria-label="Já assisti" class="icon completed" data-action="completed" title="Já assisti" type="button"></button>
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
            <!--
            <div class="review">
                <div>Minha análise</div>
                <textarea maxlength="255"></textarea>
                <button>Editar análise</button>
            </div>-->

            <script>

                const movie = {
                    listed: <?= $movie['listed'] ?? 0 ?>,
                    completed: <?= $movie['completed'] ?? 0 ?>,
                    liked: <?= $movie['liked'] ?? 0 ?>,
                    rating: <?= $movie['rating'] ?? 0 ?>
                };

                document.querySelectorAll('.toggle button').forEach(item =>
                {
                    if (movie[item.dataset.action])
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

                        if (item.dataset.action == 'liked' && item.dataset.value == 1)
                        {
                            //console.log(1);
                            //document.querySelector('.icon.completed').click();
                        }
                    });
                });

                document.querySelectorAll('.rating button').forEach(item =>
                {
                    if (movie.rating >= item.dataset.value)
                    {
                        item.classList.add('starred');
                    }

                    item.addEventListener('click', () =>
                    {
                        const value = (movie.rating == item.dataset.value) ? 0 : item.dataset.value;
                        movie.rating = value;

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


    <?php if ($movie['cast']): ?>
    <div class="cast flex_row">
        <div class="label">Elenco</div>
        <div class="container">
        <?php foreach ($movie['cast'] as $actor): ?>
            <div class="item">
                <div class="image">
                    <a href="movies/search?q=ator:<?= $actor['name'] ?>">
                        <?php if ($actor['media']): ?>
                        <img alt="" src="images/people/<?= $actor['media'] ?>.webp" />
                        <?php else: ?>
                        <img alt="" src="noimage.png" />
                        <?php endif ?>
                    </a>
                </div>
                <div class="name"><?= $actor['name'] ?></div>
                <div class="character">
                <?php foreach (explode(';', $actor['movie_character']) as $character): ?>
                    <div><?= $character ?></div>
                <?php endforeach ?>
                </div>
            </div>
        <?php endforeach ?>
        </div>
    </div>
    <?php endif ?>


    <?php if ($movie['friends']): ?>
    <div class="friends flex_row">
        <div class="label">Avaliação dos amigos</div>
        <div class="container">
        <?php foreach ($movie['friends'] as $friend): ?>
            <div class="item">
                <div class="flex_column">
                    <div class="flex_row">
                        <div class="image">
                            <a href="friend/<?= $friend['user_id'] ?>/movielist">
                                <img alt="" src="https://avatars.steamstatic.com/<?= $friend['avatarhash'] ?>_full.jpg" />
                            </a>
                        </div>
                        <div class="personaname"><?= $friend['personaname'] ?></div>
                    </div>
                    <div class="flex_row">
                        <div class="flex_column">
                            <?php if ($friend['listed']): ?>
                            <div aria-label="Quer assistir" class="icon listed friend" title="Quer assistir"></div>
                            <?php else: ?>
                            <div class="icon listed friend disable"></div>
                            <?php endif ?>
                        </div>

                        <div class="flex_column">
                            <?php if ($friend['completed']): ?>
                            <div aria-label="Já assistiu" class="icon completed friend" title="Já assistiu"></div>
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
            <a href="movie/update/<?= $movie['id'] ?>">Editar filme</a>
        </div>
    <?php endif ?>

    <?php if ($movie['related_movies']): ?>
    <div class="related flex_row">
        <div class="label">Filmes relacionados ou semelhantes</div>
        <div class="container">
        <?php foreach ($movie['related_movies'] as $item): ?>
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
</div>
<script>

const movie_id = '<?= $movie['id'] ?>';

function reaction(button)
{
    const action = button.dataset.action;
    const value = button.dataset.value;

    requestJSON(`api/v1/mylist/movie/${movie_id}`, 'post',
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
    requestJSON(`api/v1/mylist/movie/${movie_id}`, 'post',
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