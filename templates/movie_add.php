<div class="forms">
    <input id="title_br" placeholder="Título em português" type="text" />
    <input id="title_us" placeholder="Título em inglês" type="text" />
    <input id="director" list="directors" placeholder="Diretor" type="text" />
    <input id="genres" placeholder="Gêneros" type="text" />
    <input id="release_year" placeholder="Ano de lançamento" type="number" />
    <input id="imdb" placeholder="Link IMDB" type="text" />
    <button type="submit">Adicionar</button>
</div>

<script src="default.js"></script>
<script>

const title_br = document.querySelector('#title_br');
const title_us = document.querySelector('#title_us');
const director = document.querySelector('#director');
const genres = document.querySelector('#genres');
const release_year = document.querySelector('#release_year');
const imdb = document.querySelector('#imdb');
const submit = document.querySelector('.forms [type="submit"]');

get_directors();

function add_movie(body)
{
    fetch('api/v1/movie',
    {
        body: JSON.stringify(body),
        headers:
        {
            //'Authorization': `ApiKey ${apikey}`,
            'Content-Type': 'application/json'
        },
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
        if (json.hasOwnProperty('status'))
        {
            notification(json.status, {success: 'Sucesso.', failure: 'Falha.'});

            if (json.status == 'success')
            {
                clear_form();
            }
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

function clear_form()
{
    title_br.value = '';
    title_us.value = '';
    director.value = '';
    genres.value = '';
    release_year.value = '';
    imdb.value = '';
}

function get_directors()
{
    fetch('directors.json')
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
        const container = document.createElement('datalist');
        container.setAttribute('id', 'directors');

        json.directors.forEach(item =>
        {
            option = document.createElement('option');
            option.textContent = item;

            container.appendChild(option);
        });

        document.body.appendChild(container);

    })
    .catch(error =>
    {
        console.error('Erro ao carregar:', error);
    });
}

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

    add_movie(body);
});

</script>