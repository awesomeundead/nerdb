<div class="flex_row" id="my_movie_list_checkbox">
    <div class="flex_column vcenter">
        <input data-filter="watchlist" id="watchlist_checkbox" type="checkbox" />
        <label for="watchlist_checkbox">Filmes que eu quero assistir</label>
    </div>        
    <div class="flex_column vcenter">
        <input data-filter="watched" id="watched_checkbox" type="checkbox" />
        <label for="watched_checkbox">Filmes assistidos</label>
    </div>
    <div class="flex_column vcenter">
        <input data-filter="liked" id="liked_checkbox" type="checkbox" />
        <label for="liked_checkbox">Filmes que eu gostei</label>
    </div>
    <div class="flex_column vcenter">
        <input data-filter="rating" id="rating_checkbox" type="checkbox" />
        <label for="rating_checkbox">Filmes que eu melhor avaliei</label>
    </div>
</div>
<div id="my_movie_list">
    <div class="grid"></div>
</div>

<template>
    <div class="item flex_row">
        <div class="flex_column">
            <div class="image">
                <a href="">
                    <img alt="" src="" />
                </a>
            </div>
            <div class="flex_row">
                <div aria-label="Quero assistir" class="icon watchlist" title="Quero assistir">
                    <img alt="" src="saved.png" />
                </div>
                <div aria-label="Já assisti" class="icon watched" title="Já assisti">
                    <img alt="" src="watched.png" />
                </div>
                <div aria-label="Gostei" class="icon liked" title="Gostei">
                    <img alt="" src="liked.png" />
                </div>
                <div aria-label="Minha avaliação" class="icon_rating rating" title="Minha avaliação"></div>
            </div>
        </div>
        <div class="flex_row">
            <div>
                <div class="title_br"></div>
            </div>
        </div>
    </div>
</template>

<script>

const container = document.querySelector('#my_movie_list .grid');
const template = document.querySelector('template');
const filter = {};

document.querySelectorAll('#watchlist_checkbox, #watched_checkbox, #liked_checkbox, #rating_checkbox').forEach(item =>
{
    item.addEventListener('click', () =>
    {
        if (item.checked)
        {
            filter[item.dataset.filter] = 1;
        }
        else
        {
            delete filter[item.dataset.filter];
        }

        load_movies();
    });
});

load_movies();

function load_movies()
{
    let url = 'api/v1/movie-list/my';

    if (Object.keys(filter).length)
    {
        url += '?' + new URLSearchParams(filter).toString();
    }

    console.log(url);

    fetch(url)
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
    if (!movies.length)
    {
        container.innerHTML = '<div class="centralizado">Não foram encontrados filmes nesta lista.</div>';

        return;
    }
    
    container.innerHTML = '';

    movies.forEach(item =>
    {
        const clone = template.content.cloneNode(true);

        clone.querySelector('a').href = `movie/${item.id}`;        
        clone.querySelector('img').src = (item.media == '') ? 'public/noimage.png' : `public/images/256/${item.media}.webp`;
        clone.querySelector('.title_br').textContent =  item.title_br;

        if (!item.watchlist || item.watched)
        {
            clone.querySelector('.watchlist').remove();
        }

        if (!item.watched)
        {
            clone.querySelector('.watched').remove();
        }

        if (!item.liked)
        {
            clone.querySelector('.liked').remove();
        }

        if (item.rating)
        {
            clone.querySelector('.rating').textContent = item.rating;
        }
        else
        {
            clone.querySelector('.rating').remove();
        }

        container.appendChild(clone);
    });
}

</script>