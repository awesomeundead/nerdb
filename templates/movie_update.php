<div class="forms">
    <div class="image">
        <img alt="" height="256" id="media" src="" />
    </div>
    <input id="title_br" placeholder="Título em português" type="text" />
    <input id="title_us" placeholder="Título em inglês" type="text" />
    <input id="director" placeholder="Diretor" type="text" />
    <input id="genres" placeholder="Gêneros" type="text" />
    <input id="release_year" placeholder="Ano de lançamento" type="number" />
    <input id="imdb" placeholder="Link IMDB" type="text" />
    <button type="submit">Atualizar</button>
</div>

<script src="default.js"></script>
<script>

const movie_id = '<?= $movie_id ?>';

const media = document.querySelector('#media');
const title_br = document.querySelector('#title_br');
const title_us = document.querySelector('#title_us');
const director = document.querySelector('#director');
const genres = document.querySelector('#genres');
const release_year = document.querySelector('#release_year');
const imdb = document.querySelector('#imdb');
const submit = document.querySelector('.forms [type="submit"]');

submit.addEventListener('click', () =>
{
    submit.disabled = true;

    const body =
    {
        'title_br': title_br.value,
        'title_us': title_us.value,
        'director': director.value,
        'genres': genres.value,
        'release_year': release_year.value,
        'imdb': imdb.value
    };

    update_movie(body);
});

load_movie();

function load_movie()
{
    fetch(`api/v1/movie/${movie_id}`)
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
        render(json);
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function update_movie(body)
{
    fetch(`api/v1/movie/${movie_id}`,
    {
        body: JSON.stringify(body),
        headers:
        {
            //'Authorization': `ApiKey ${apikey}`,
            'Content-Type': 'application/json'
        },
        method: 'put'
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
        if (json.hasOwnProperty('status'))
        {
            notification(json.status, {success: 'Sucesso.', failure: 'Falha.'});
        }
        else
        {
            console.log(json);
        }

        submit.disabled = false;
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function render(item)
{
    media.src = (item.media == '') ? 'public/noimage.png' : `public/images/256/${item.media}.webp`;
    title_br.value = item.title_br,
    title_us.value = item.title_us,
    director.value = item.director,
    genres.value = item.genres,
    release_year.value = item.release_year,
    imdb.value = item.imdb;
}

</script>