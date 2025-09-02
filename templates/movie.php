<div id="movie">
    <div class="flex_column wrap">
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
                <div>Gêneros:</div>
                <div class="genres flex_column"></div>
            </div>
            <div class="flex_column">
                <div>Ano de lançamento:</div>
                <div class="release_year"></div>
            </div>
            <div class="flex_column">
                <div>Diretor:</div>
                <div class="director flex_row"></div>
            </div>
            <div class="imdb">
                <a href="" target="_blank">IMDB</a>
            </div>
            <div class="platforms flex_column"></div>
            <?php if ($session->logged_in ?? false): ?>
            <div class="toggle flex_column">
                <button aria-label="Quero assistir" class="icon watchlist" data-action="watchlist" title="Quero assistir" type="button"></button>
                <button aria-label="Já assisti" class="icon watched" data-action="watched" title="Já assisti" type="button"></button>
                <button aria-label="Gostei" class="icon like" data-action="liked" title="Gostei" type="button"></button>
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
        </div>
    </div>
    <div class="flex_row">
        <div>Elenco</div>
        <div class="cast_container flex_column wrap"></div>
    </div>
    <?php if ($session->logged_in ?? false): ?>
        <div class="update">
            <a href="">Editar filme</a>
        </div>
    <?php endif ?>
</div>
<template class="cast">
    <div class="actor">
        <div class="image">
            <a>
                <img height="128" />
            </a>
        </div>
        <div class="name"></div>
        <div class="character"></div>
    </div>
</template>
<script>

const movie_id = routeSegments[1];
const url = new URL(`api/v1/movie/${movie_id}`, document.baseURI);
const template_cast = document.querySelector('template.cast');
const cast_container = document.querySelector('.cast_container');

const render = function(movie)
{
    const directors = movie.director.split(';');

    directors.forEach(director =>
    {
        document.querySelector('.director').innerHTML += `<a href="movies?q=diretor:${director}">${director}</a>`;
    });

    movie.cast.forEach(item =>
    {
        const clone = template_cast.content.cloneNode(true);

        clone.querySelector('.image img').src = item.media?.trim() ? `images/people/${item.media}.webp` : 'noimage.png';
        clone.querySelector('.image a').href = `movies?q=ator:${item.name}`;
        clone.querySelector('.name').textContent = item.name;

        const characters = item.movie_character.split(';');

        characters.forEach(character =>
        {
            clone.querySelector('.character').innerHTML += `<div>${character}</div>`;
        });

        cast_container.appendChild(clone);
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
            reaction(item);
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

    movie.platforms.forEach(platform =>
    {
        const element = document.createElement('a');
        element.href = get_platform(platform.platform_name, platform.platform_link);
        element.setAttribute('target', '_blank');
        element.textContent = platform.platform_name;
        document.querySelector('.platforms').appendChild(element);
    });
}

getJSON();

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

function get_platform(name, link)
{
    const platforms = {
        'Prime Video': `https://www.primevideo.com/detail/${link}`,
        'HBO Max': `https://play.hbomax.com/movie/${link}`
    }

    return platforms[name];
}

</script>