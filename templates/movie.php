<div id="movie">
    <div class="image">
        <img alt="" height="512" src="" />
    </div>
    <div class="flex_row">
        <div>
            <div class="title_br"></div>
        </div>
        <div class="flex_column">
            <div>Título em inglês:</div>
            <div class="title_us"></div>
        </div>
        <div class="flex_column">
            <div>Diretor:</div>
            <div class="director flex_column"></div>
        </div>
        <div class="flex_column">
            <div>Gêneros:</div>
            <div class="genres flex_column"></div>
        </div>
        <div class="flex_column">
            <div>Lançamento:</div>
            <div class="release_year"></div>
        </div>
        <div class="imdb">
            <a href="" target="_blank">IMDB</a>
        </div>
        <?php if ($session->logged_in ?? false): ?>
        <div class="update">
            <a href="">Editar</a>
        </div>
        <div class="toggle flex_column">
            <button aria-label="Quero assistir" class="icon" data-action="watchlist" type="button">
                <img alt="" src="saved.png" />
                <span>Quero assistir</span>
            </button>
            <button aria-label="Já assisti" class="icon" data-action="watched" type="button">
                <img alt="" src="watched.png" />
                <span>Já assisti</span>
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
        <div class="platforms flex_column"></div>
        <?php endif ?>
    </div>
</div>
<script>

const movie_id = '<?= $movie_id ?>';
const url = `api/v1/movie/${movie_id}`;

const render = function(movie)
{
    const directors = movie.director.split(';');

    directors.forEach(director =>
    {
        const element = document.createElement('a');
        element.href = `movies?q=diretor:${director}`;
        element.textContent = director;
        document.querySelector('.director').appendChild(element);
    });

    const genres = movie.genres.split(';');

    genres.forEach(genre =>
    {
        const element = document.createElement('a');
        element.href = `movies?q=genero:${genre}`;
        element.textContent = genre;
        document.querySelector('.genres').appendChild(element);
    });

    document.title = `${movie.title_br} (${movie.release_year})`;
    document.querySelector('.image img').src = movie.media?.trim() ? `images/512/${movie.media}.webp` : 'noimage.png';
    document.querySelector('.title_br').textContent =  movie.title_br;
    document.querySelector('.title_us').textContent =  movie.title_us;
    document.querySelector('.release_year').textContent =  movie.release_year;
    document.querySelector('.imdb a').href =  `https://www.imdb.com/pt/title/${movie.imdb}/`;
    document.querySelector('.update a').href =  `movie/update/${movie.id}`;

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
            addmovie(movie.id, item);
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

            rating_movie(value);
        });
    });

    movie.platforms.forEach(platform =>
    {
        const element = document.createElement('a');
        element.href = get_platform(platform.platform_name, platform.platform_link);
        element.setAttribute('target', '_blank');
        element.textContent = platform.platform_name;
        document.querySelector('.platforms').appendChild(element);
    });
}

load_movie(url, render);

function addmovie(movie_id, button)
{
    const action = button.dataset.action;
    const value = button.dataset.value;

    fetch(`api/v1/user/addmovie/${movie_id}?${action}=${value}`,
    {
        method: 'post'
    })
    .then(response =>
    {
        if (!response.ok)
        {
            throw new Error(response.statusText);
        }

        return response.json();
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

function rating_movie(value)
{
    fetch(`api/v1/user/addmovie/${movie_id}?rating=${value}`,
    {
        method: 'post'
    })
    .then(response =>
    {
        if (!response.ok)
        {
            throw new Error(response.statusText);
        }

        return response.json();
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

function get_platform(name, link)
{
    const platforms = {
        'Prime Video': `https://www.primevideo.com/detail/${link}`,
        'HBO Max': `https://play.hbomax.com/movie/${link}`
    }

    return platforms[name];
}

</script>