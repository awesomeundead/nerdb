<div id="movie">
    <div class="grid">
        <div class="image flex_row">
            <img alt="" height="512" src="" />
        </div>
        <div class="flex_row">
            <div>
                <h1 class="title_br"></h1>
            </div>
            <div class="listing">
                <div>Título em inglês</div>
                <div class="title_us"></div>
            </div>
            <div class="listing">
                <div>Gêneros:</div>
                <div class="genres flex_column"></div>
            </div>
            <div class="listing">
                <div>Ano de lançamento</div>
                <div class="release_year"></div>
            </div>
            <div class="listing">
                <div>Diretor</div>
                <div class="director flex_row"></div>
            </div>
            <div class="listing services">
                <div>Serviços</div>
                <div class="container">
                    <a class="imdb" target="_blank">
                        <img src="icon_imdb_logo.png" />
                    </a>
                </div>
            </div>
            <?php if ($session->logged_in ?? false): ?>
            <div class="toggle flex_column">
                <button aria-label="Quero assistir" class="icon watchlist" data-action="watchlist" title="Quero assistir" type="button"></button>
                <button aria-label="Já assisti" class="icon watched" data-action="watched" title="Já assisti" type="button"></button>
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
            <?php endif ?>
        </div>
    </div>
    <div class="cast flex_row">
        <div class="label">Elenco</div>
        <div class="container"></div>
    </div>
    <div class="friends flex_row">
        <div class="label">Avaliação dos amigos</div>
        <div class="container flex_column wrap"></div>
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
<template class="friends">
    <div class="item">
        <div class="flex_column">
            <div>
                <div class="image">
                    <a>
                        <img  />
                    </a>
                </div>
                <div class="personaname"></div>
            </div>
            <div class="flex_row">
                <div class="flex_column">
                    <div aria-label="Quer assistir" class="icon friend friend_watchlist" title="Quer assistir">
                        <img alt="" src="saved.png" />
                    </div>
                </div>

                <div class="flex_column">
                    <div aria-label="Já assistiu" class="icon friend friend_watched" title="Já assistiu">
                        <img alt="" src="watched.png" />
                    </div>
                </div>

                <div class="flex_column">
                    <div aria-label="Gostou" class="icon friend friend_liked" title="Gostou">
                        <img alt="" src="liked.png" />
                    </div>
                </div>

                <div class="flex_column">
                    <div aria-label="Avaliação" class="icon_rating friend friend_rating" title="Avaliação"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

const movie_id = routeSegments[1];
const url = new URL(`api/v1/movie/${movie_id}`, document.baseURI);
const template_cast = document.querySelector('template.cast');
const template_friends = document.querySelector('template.friends');
const cast_container = document.querySelector('.cast .container');
const friends_container = document.querySelector('.friends .container');

const render = function(movie)
{
    const directors = movie.director.split(';');

    directors.forEach(director =>
    {
        document.querySelector('.director').innerHTML += `<a href="movies/search?q=diretor:${director}">${director}</a>`;
    });

    if (!movie.cast.length)
    {
        cast_container.parentNode.remove();
    }

    movie.cast.forEach(item =>
    {
        const clone = template_cast.content.cloneNode(true);

        clone.querySelector('.image img').src = item.media?.trim() ? `images/people/${item.media}.webp` : 'noimage.png';
        clone.querySelector('.image a').href = `movies/search?q=ator:${item.name}`;
        clone.querySelector('.name').textContent = item.name;

        const characters = item.movie_character.split(';');

        characters.forEach(character =>
        {
            clone.querySelector('.character').innerHTML += `<div>${character}</div>`;
        });

        cast_container.appendChild(clone);
    });

    const disable = (clone, selector, condition = true) =>
    {
        const element = clone.querySelector(selector);

        if (condition)
        {
            element.classList.add('disable');
        }
    }    

    if (!movie.friends.length)
    {
        friends_container.parentNode.remove();
    }

    movie.friends.forEach(item =>
    {
        const clone = template_friends.content.cloneNode(true);

        clone.querySelector('.image img').src = `https://avatars.steamstatic.com/${item.avatarhash}_full.jpg`;
        clone.querySelector('.image a').href = `friends/movielist/${item.user_id}`;
        clone.querySelector('.personaname').textContent = item.personaname;

        if (!item.watchlist)
        {
            disable(clone, '.friend_watchlist');
        }

        disable(clone, '.friend_watched', !item.watched);
        disable(clone, '.friend_liked', !item.liked);

        if (item.rating)
        {
            clone.querySelector('.friend_rating').textContent = item.rating;
        }
        else
        {
            disable(clone, '.friend_rating');
        }

        friends_container.appendChild(clone);
    });

    const genres = movie.genres.split(';');

    genres.forEach(genre =>
    {
        const element = document.createElement('a');
        element.href = `movies/search?q=genero:${genre}`;
        element.textContent = genre;
        document.querySelector('.genres').appendChild(element);
    });

    document.title = `${movie.title_br} (${movie.release_year})`;
    document.querySelector('.image img').src = movie.media?.trim() ? `images/512/${movie.media}.webp` : 'noimage.png';
    document.querySelector('.title_br').textContent =  movie.title_br;
    document.querySelector('.title_us').textContent =  movie.title_us;
    document.querySelector('.release_year').textContent =  movie.release_year;
    document.querySelector('.imdb').href =  `https://www.imdb.com/pt/title/${movie.imdb}/`;
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

    movie.platforms.forEach(item =>
    {
        const platform = get_platform(item.platform_name, item.platform_id);
        const element = document.createElement('a');
        const image = document.createElement('img');
        element.href = platform.url;
        image.alt = platform.name;
        image.src = platform.icon;
        element.setAttribute('target', '_blank');
        element.appendChild(image);
        document.querySelector('.services .container').appendChild(element);
    });

    if (!movie.platforms.length)
    {
        //document.querySelector('.platforms').remove();
    }
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

function get_platform(name, id)
{
    const platforms = {
        hbomax:
        {
            name: 'HBO Max',
            url: `https://play.hbomax.com/movie/${id}`,
            icon: 'icon_hbomax_logo.png'
        },
        primevideo: 
        {
            name: 'Prime Video',
            url: `https://www.primevideo.com/detail/${id}`,
            icon: 'icon_primevideo_logo.png'
        }
    }

    return platforms[name];
}

</script>