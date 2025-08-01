<form class="forms">
    <div class="image">
        <img alt="" height="256" id="media" src="" />
    </div>
    <input name="title_br" placeholder="Título em português" type="text" />
    <input name="title_us" placeholder="Título em inglês" type="text" />
    <input name="director" placeholder="Diretor" type="text" />
    <input name="genres" placeholder="Gêneros" type="text" />
    <input name="release_year" placeholder="Ano de lançamento" type="number" />
    <input name="imdb" placeholder="Link IMDB" type="text" />
    <button type="submit">Atualizar</button>
</form>

<script src="default.js"></script>
<script>

const movie_id = '<?= $movie_id ?>';
const url = `api/v1/movie/${movie_id}`;
const media = document.querySelector('#media');
const form = document.querySelector('form.forms');

const render = function(item)
{
    media.src = item.media?.trim() ? `images/256/${item.media}.webp` : 'noimage.png';
    form.elements['title_br'].value = item.title_br,
    form.elements['title_us'].value = item.title_us,
    form.elements['director'].value = item.director,
    form.elements['genres'].value = item.genres,
    form.elements['release_year'].value = item.release_year,
    form.elements['imdb'].value = item.imdb;
}

form.addEventListener('submit', e =>
{
    e.preventDefault();
    form.querySelector('[type="submit"]').disabled = true;

    update_movie(Object.fromEntries(new FormData(form)));
});

load_movie(url, render);

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

        form.querySelector('[type="submit"]').disabled = false;
    })
    .catch(error =>
    {
        form.querySelector('[type="submit"]').disabled = false;
        console.error(error);
    });
}

</script>