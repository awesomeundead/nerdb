function notification(status, message)
{
    const card = document.createElement('div');
    const container = document.querySelector('body');

    card.innerHTML = message[status]; 
    card.classList.add('notification', status);   
    container.insertBefore(card, container.firstChild);

    setTimeout(() =>
    {
        card.remove();

    }, 1000 * 3);
}

function pagination()
{
    document.querySelectorAll('.pagination button').forEach(item =>
    {
        item.addEventListener('click', () =>
        {
            if (item.className == 'previous')
            {
                if (previous_offset == null)
                {
                    return;
                }

                offset = previous_offset;
            }
            
            if (item.className == 'next')
            {
                if (next_offset == null)
                {
                    return;
                }

                offset = next_offset;
            }
            
            const url = new URL(window.location.href);
            url.searchParams.set('offset', offset);
            history.pushState({}, '', url.toString());

            searchParams.set('offset', offset);
            getJSON();
        });
    });
}

function requestJSON(url, method = 'get', body = null)
{
    return new Promise((resolve, reject) =>
    {
        if (body instanceof Object)
        {
            body = JSON.stringify(body);
        }

        const options =
        {
            body: body,
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            method: method
        };

        fetch(url, options)
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
            resolve(json);
        })
        .catch(error =>
        {
            reject(error);
        });
    })
}

function get_directors()
{
    requestJSON('directors.json')
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

function getJSON()
{
    requestJSON(url)
    .then(json =>
    {
        render(json)
    })
    .catch(error =>
    {
        console.error('Erro ao carregar:', error);
    });
}

function sendForm(form, method)
{
    const submit = form.querySelector('[type="submit"]');
    
    form.addEventListener('submit', e =>
    {
        e.preventDefault();
        submit.disabled = true;

        let body = Object.fromEntries(new FormData(form));
        
        requestJSON(url, method, body)
        .then(json =>
        {
            if (json.hasOwnProperty('message'))
            {
                alert(json.message);
            }

            if (json.hasOwnProperty('status'))
            {
                notification(json.status, {success: 'Sucesso.', failure: 'Falha.'});

                if (method == 'post' && json.status == 'success')
                {
                    form.reset();
                }
            }

            submit.disabled = false;
        })
        .catch(error =>
        {
            submit.disabled = false;
            console.error('Erro ao carregar:', error);
        });
    });
}

const addForm = (form) => sendForm(form, 'post');
const updateForm = (form) => sendForm(form, 'put');

const relativePath = window.location.href.substring(document.baseURI.length);
const routeSegments = relativePath ? relativePath.split('/') : [];