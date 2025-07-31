<div class="grid" id="movies"></div>
<template>
    <div class="item flex_column">
        <div class="image">
            <a href="">
                <img alt="" src="" />
            </a>
        </div>
        <div class="info flex_row">
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
                <div>Lançamento:</div>
                <div class="release_year"></div>
            </div>
            <div class="imdb">
                <a href="" target="_blank">IMDB</a>
            </div>
        </div>
    </div>
</template>

<script>

const query = new URLSearchParams(window.location.search);
const container = document.querySelector('#movies');
const template = document.querySelector('template');
const search = document.querySelector('#search');

search.addEventListener('submit', e =>
{
    e.preventDefault();

    const url = new URL(window.location);
    url.search = `q=${search.q.value}`;
    window.history.replaceState({}, '', url);

    search_movie(search.q.value);
});

if (query.has('q'))
{
    search.q.value = query.get('q');
    search_movie(query.get('q'));
}
else
{
    load_movies('?order=random');

    document.title = 'Filmes aleatórios';
}

function search_movie(value)
{
    if (value.match(/^\d{4}$/))
    {
        load_movies(`?release=${value}`);
    }
    else if (match = value.match(/^diretor:(.+)/))
    {
        load_movies(`?director=${match[1]}`);

        value = match[1];
    }
    else
    {
        load_movies(`?search=${value}`);
    }

    document.title = value;
}

function load_movies(url)
{
    fetch('api/v1/movies' + url)
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
        render_movies(json.movies)
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function render_movies(movies)
{
    container.innerHTML = '';

    movies.forEach(item =>
    {
        const clone = template.content.cloneNode(true);
        const directors = item.director.split(';');

        directors.forEach(director =>
        {
            const element = document.createElement('a');
            element.href = `movies?q=diretor:${director}`;
            element.textContent = director;
            clone.querySelector('.director').appendChild(element);
        });

        clone.querySelector('a').href = `movie/${item.id}`;
        clone.querySelector('img').src = (item.media == '') ? 'public/noimage.png' : `public/images/256/${item.media}.webp`;
        clone.querySelector('.title_br').textContent =  item.title_br;
        clone.querySelector('.title_us').textContent =  item.title_us;
        clone.querySelector('.release_year').textContent =  item.release_year;
        clone.querySelector('.imdb a').href =  `https://www.imdb.com/pt/title/${item.imdb}/`;

        container.appendChild(clone);
    });
}

</script>