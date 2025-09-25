class ContentList
{
    constructor({ url, containerSelector, templateSelector, render, pagination = false})
    {
        this.baseURL = new URL(url, document.baseURI);
        this.container = document.querySelector(containerSelector);
        this.template = document.querySelector(templateSelector);
        this.render = render;
        this.pagination = pagination;

        if (pagination)
        {
            this.offset = new URLSearchParams(window.location.search).get('offset') || 0;
            this.previous_offset = null;
            this.next_offset = null
        }

        if (!this.container || !this.template)
        {
            throw new Error('Container ou template não encontrados.');
        }
    }

    setQueryParams(params = {})
    {
        for (const [key, value] of Object.entries(params))
        {
            this.baseURL.searchParams.set(key, value);
        }
    }

    removeQueryParam(key)
    {
        this.baseURL.searchParams.delete(key);
    }

    next()
    {
        if (this.pagination && this.next_offset != null)
        {
            this.setOffset(this.next_offset);
            this.init();
        }
    }

    previous()
    {
        if (this.pagination && this.previous_offset != null)
        {
            this.setOffset(this.previous_offset);
            this.init();
        }
    }

    resetOffset()
    {
        if (this.pagination)
        {
            this.offset = 0;
            this.previous_offset = null;
            this.next_offset = null
        }

        const url = new URL(window.location.href);
        url.searchParams.delete('offset');
        history.pushState({}, '', url.toString());
    }

    setOffset(offset)
    {
        if (this.pagination)
        {
            this.offset = offset;
            this.baseURL.searchParams.set('offset', offset);

            const url = new URL(window.location.href);
            url.searchParams.set('offset', offset);
            history.pushState({}, '', url.toString());
        }
    }

    updatePaginationControls()
    {
        const paginationCard = document.querySelector('.pagination');

        if (!paginationCard) return;

        console.log(this.previous_offset, this.next_offset);

        paginationCard.hidden = (this.previous_offset == null && this.next_offset == null);
        paginationCard.querySelector('.previous').disabled = this.previous_offset == null;
        paginationCard.querySelector('.next').disabled = this.next_offset == null;
    }


    async fetchData(method = 'GET', body = null)
    {
        const options = {
            method,
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: body ? JSON.stringify(body) : null
        };

        const response = await fetch(this.baseURL, options);

        if (!response.ok)
        {
            throw new Error(response.statusText);
        }

        return await response.json();
    }

    async init()
    {
        try
        {
            if (this.pagination)
            {
                this.baseURL.searchParams.set('offset', this.offset);
            }

            const data = await this.fetchData();

            if (this.pagination)
            {
                this.previous_offset = data.previous_offset;
                this.next_offset = data.next_offset;
                this.updatePaginationControls();
            }

            this.render(data, this.container, this.template);
        }
        catch (error)
        {
            console.error('Erro ao carregar lista:', error);
        }
    }
}

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

function sendForm(url, form, method)
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

const addForm = (url, form) => sendForm(url, form, 'post');
const updateForm = (url, form) => sendForm(url, form, 'put');

const query = new URLSearchParams(window.location.search);
const relativePath = window.location.href.substring(document.baseURI.length);
const routeSegments = relativePath ? relativePath.split('/') : [];