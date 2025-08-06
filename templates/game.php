<div id="movie">
    <div class="image">
        <img alt="" height="512" src="" />
    </div>
    <div class="flex_row">
        <div>
            <div class="title"></div>
        </div>
        <div class="flex_column">
            <div>Gêneros:</div>
            <div class="genres flex_column"></div>
        </div>
        <div class="flex_column">
            <div>Lançamento:</div>
            <div class="release_year"></div>
        </div>
        <div class="steam">
            <a target="_blank">Steam</a>
        </div>
        <?php if ($session->logged_in ?? false): ?>
        <div class="update">
            <a href="">Editar</a>
        </div>
        <div class="toggle flex_column">
            <button aria-label="Quero assistir" class="icon" data-action="playlist" type="button">
                <img alt="" src="saved.png" />
                <span>Quero jogar</span>
            </button>
            <button aria-label="Já assisti" class="icon" data-action="played" type="button">
                <img alt="" src="watched.png" />
                <span>Joguei</span>
            </button>
            <button aria-label="Gostei" class="icon" data-action="liked" type="button">
                <img alt="" src="liked.png" />
                <span>Gostei</span>
            </button>
        </div>
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
        <?php endif ?>
        <div class="flex_column">
            <div>Desenvolvedor:</div>
            <div class="developer flex_row"></div>
        </div>
    </div>
</div>
<script>

const game_id = '<?= $game_id ?>';
const url = new URL(`api/v1/game/${game_id}`, document.baseURI);

const render = function(game)
{
    const developers = game.developer.split(';');

    developers.forEach(developer =>
    {
        const element = document.createElement('a');
       // element.href = `games?q=desenvolvedor:${developer}`;
        element.textContent = developer;
        document.querySelector('.developer').appendChild(element);
    });

    const genres = game.genres.split(';');

    genres.forEach(genre =>
    {
        const element = document.createElement('a');
        //element.href = `games?q=genero:${genre}`;
        element.textContent = genre;
        document.querySelector('.genres').appendChild(element);
    });

    document.title = `${game.title} (${game.release_year})`;
    document.querySelector('.image img').src = game.media?.trim() ? `images/512/${game.media}.webp` : 'noimage.png';
    document.querySelector('.title').textContent =  game.title;
    document.querySelector('.release_year').textContent =  game.release_year;
    document.querySelector('.steam a').href =  `https://steampowered.com./app/${game.steam}/`;
    document.querySelector('.update a').href =  `game/update/${game.id}`;

    if (!game.steam)
    {
        document.querySelector('.steam').remove();
    }

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
}

getJSON();

function reaction(button)
{
    const action = button.dataset.action;
    const value = button.dataset.value;

    sendJSON('post', `api/v1/mylist/game/${game_id}`,
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
    sendJSON('post', `api/v1/mylist/game/${game_id}`,
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