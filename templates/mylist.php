<?php $this->insert('components/my_user_panel.php') ?>
<div id="userlist">
    <div class="filter flex_row">
        <div class="flex_column vcenter">
            <input data-filter="listed" id="listed_checkbox" type="checkbox" />
            <label data-game="Quero jogar" data-movie="Quero assistir" for="listed_checkbox" id="listed_label"></label>
        </div>        
        <div class="flex_column vcenter">
            <input data-filter="completed" id="completed_checkbox" type="checkbox" />
            <label data-game="Joguei" data-movie="Assisti" for="completed_checkbox" id="completed_label"></label>
        </div>
        <div class="flex_column vcenter">
            <input data-filter="liked" id="liked_checkbox" type="checkbox" />
            <label for="liked_checkbox">Gostei</label>
        </div>
        <div class="flex_column vcenter">
            <input data-filter="rating" id="rating_checkbox" type="checkbox" />
            <label for="rating_checkbox">Melhor avaliei</label>
        </div>
    </div>
    <div class="pagination">
        <div class="content">
            <button class="previous" type="button">Anterior</button>
            <button class="next" type="button">Próximo</button>
        </div>
    </div>
    <div class="container"></div>
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
                <div class="flex_column">
                    <div class="icon listed me" data-game="Quero jogar" data-movie="Quero assistir"></div>
                </div>

                <div class="flex_column">
                    <div class="icon completed me" data-game="Joguei" data-movie="Já assisti"></div>
                </div>

                <div class="flex_column">
                    <div class="icon liked me" title="Gostei"></div>
                </div>

                <div class="flex_column">
                    <div class="icon rating me" title="Minha avaliação"></div>
                </div>
            </div>
        </div>
        <div class="flex_row">
            <div>
                <div class="title title_br"></div>
            </div>
        </div>
    </div>
</template>

<script>

const listed_label = document.querySelector('#listed_label');
const completed_label = document.querySelector('#completed_label');
const content =
{
    gamelist: new ContentList(
    {
        url: `api/v1/mylist/games`,
        containerSelector: '#userlist .container',
        templateSelector: 'template',
        pagination: true,
        render: (json, container, template) =>
        {
            renderItems(
            {
                items: json.games || [],
                container,
                template,
                type: 'game'
            });

            document.title = `Minha lista de jogos | ${document.title}`;
        }
    }),
    movielist: new ContentList(
    {
        url: `api/v1/mylist/movies`,
        containerSelector: '#userlist .container',
        templateSelector: 'template',
        pagination: true,
        render: (json, container, template) =>
        {
            renderItems(
            {
                items: json.movies || [],
                container,
                template,
                type: 'movie'
            });

            document.title = `Minha lista de filmes | ${document.title}`;
        }
    })
}

let namelist = '<?= $namelist ?>';
let list = content[namelist];

list.init();

document.querySelectorAll('.nolink').forEach(item =>
{
    item.addEventListener('click', e =>
    {
        e.preventDefault();

        const name = e.target.dataset.namelist;

        if (namelist != name)
        {
            namelist = name;
            list = content[namelist];
            list.resetOffset();
            list.init();

            routeSegments[1] = name;
            const url = new URL(document.baseURI + routeSegments.join('/'));
            history.pushState({}, '', url.toString());
        }
    });
});

document.querySelector('.pagination .next').addEventListener('click', () => list.next());
document.querySelector('.pagination .previous').addEventListener('click', () => list.previous());

document.querySelectorAll('#listed_checkbox, #completed_checkbox, #liked_checkbox, #rating_checkbox').forEach(item =>
{
    item.addEventListener('click', () =>
    {
        const key = item.dataset.filter;
        
        if (item.checked)
        {
            list.setQueryParams({ [key]: 1 });
        }
        else
        {
            list.removeQueryParam(key);
        }

        list.resetOffset();
        list.init();
    });
});

function renderItems({ items, container, template, type })
{
    listed_label.textContent = listed_label.dataset[type];
    completed_label.textContent = completed_label.dataset[type];

    if (!items.length)
    {
        container.innerHTML =
        {
            game: '<div class="centralizado">Não foram encontrados jogos nesta lista.</div>',
            movie: '<div class="centralizado">Não foram encontrados filmes nesta lista.</div>'
        }[type];

        return;
    }

    container.innerHTML = '';

    const disable = (clone, selector, condition = true) =>
    {
        const element = clone.querySelector(selector);

        if (condition)
        {
            element.classList.add('disable');
        }
    }

    const setTitle = (clone, selector) =>
    {
        const element = clone.querySelector(selector);

        if (element)
        {
            element.title = element.dataset[type]
        };
    };

    const fragment = document.createDocumentFragment();
    
    items.forEach(item =>
    {
        const clone = template.content.cloneNode(true);

        setTitle(clone, '.listed');
        setTitle(clone, '.completed');

        clone.querySelector('a').href = `${type}/${item.id}/${item.title_url}`;
        clone.querySelector('.image img').src = item.media?.trim() ? `images/${type === 'game' ? 'games/256' : '256'}/${item.media}.webp` : 'noimage.png';
        clone.querySelector('.title, .title_br').textContent = item.title || item.title_br;

        if (!item.listed)
        {
            disable(clone, '.listed');
        }

        disable(clone, '.completed', !item.completed);
        disable(clone, '.liked', !item.liked);

        if (item.rating)
        {
            clone.querySelector('.rating').textContent = item.rating;
        }
        else
        {
            disable(clone, '.rating');
        }

        fragment.appendChild(clone);
    });

    container.appendChild(fragment);
}

</script>