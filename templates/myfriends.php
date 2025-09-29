<?php $this->insert('components/my_user_panel.php') ?>
<div id="friends">
    <h1>Meus amigos</h1>
    <div class="container"></div>
</div>

<template>
    <div class="item">
        <div class="image">
            <a>
                <img />
            </a>
        </div>
        <div class="personaname"></div>
    </div>
</template>

<script>

const list = new ContentList(
{
    url: 'api/v1/user/friends',
    containerSelector: '#friends .container',
    templateSelector: 'template',
    render: (json, container, template) =>
    {
        const items = json.friends || [];

        if (!items.length)
        {
            container.innerHTML = 'Os serviços da Steam podem estar fora do ar ou o seu perfil está bloqueando a visualização de amigos.';
            return;
        }

        container.innerHTML = '';

        items.forEach(item =>
        {
            const clone = template.content.cloneNode(true);

            clone.querySelector('.image a').href = `friend/${item.id}/movielist`;
            clone.querySelector('img').src =  `https://avatars.steamstatic.com/${item.avatarhash}_full.jpg`;
            clone.querySelector('.personaname').textContent = item.personaname;

            container.appendChild(clone);
        });
    }
});

list.init();

</script>