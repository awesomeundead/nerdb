<div id="friends"></div>

<template>
    <div class="item flex_column">
        <div class="image">
            <img alt="" src="" />
        </div>
        <div class="flex_row">
            <div>
                <div class="personaname"></div>
            </div>
            <div class="movies">
                <a href="">Lista de filmes</a>
            </div>
        </div>
    </div>
</template>

<script>

const container = document.getElementById('friends');
const template = document.querySelector('template');

load_friends();

function load_friends()
{
    fetch(`api/v1/user/friends`)
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
        render_friends(json.friends);
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function render_friends(friends)
{
    container.innerHTML = '';

    friends.forEach(item =>
    {
        const clone = template.content.cloneNode(true);

        clone.querySelector('img').src =  `https://avatars.steamstatic.com/${item.avatarhash}_medium.jpg`;
        clone.querySelector('.personaname').textContent = item.personaname;
        clone.querySelector('.movies a').href = `friends/movielist/${item.id}`;

        container.appendChild(clone);
    });
}

</script>