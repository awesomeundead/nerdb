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

function getJSON()
{
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
        render(json)
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function sendJSON(method, url, body)
{
    return new Promise((resolve, reject) =>
    {
        const options =
        {
            body: JSON.stringify(body),
            headers:
            {
                'Content-Type': 'application/json'
            },
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
    });
}

function requestJSON(url)
{
    return new Promise((resolve, reject) =>
    {
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
            resolve(json);
        })
        .catch(error =>
        {
            reject(error);
        });
    })
}