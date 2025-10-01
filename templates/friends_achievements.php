<div id="profile_panel">
    <div class="image">
        <img alt="" src="https://avatars.steamstatic.com/<?= $friend['avatarhash'] ?>_full.jpg" />
    </div>
    <div class="content">
        <div class="personaname"><?= $friend['personaname'] ?></div>
        <div class="role"><?= $friend['role'] ?></div>
        <div class="created_date"><?= $friend['created_date'] ?></div>
    </div>
    <nav>
        <a data-namelist="movielist" href="friend/<?= $friend['id'] ?>/movielist">Filmes</a>
        <a data-namelist="gamelist" href="friend/<?= $friend['id'] ?>/gamelist">Jogos</a>
        <a href="friend/<?= $friend['id'] ?>/achievements">Conquistas</a>
    </nav>
</div>
<div id="achievements" class="flex_row"></div>
<template>
    <div class="item flex_row">
        <div class="flex_column">
            <div class="image friend flex_row">
                <img />
            </div>
            <div class="flex_row flex">
                <div class="title"></div>
                <div class="description"></div>
            </div>
            <div class="image my flex_row">
                <img />
            </div>
        </div>
        <div class="flex_column">
            <div class="progress friend flex_row flex">
                <div class="progress_bar"></div>
            </div>
            <div class="progress my flex_row flex">
                <div class="progress_bar"></div>
            </div>
        </div>
    </div>
</template>

<script>

const friend_id = <?= $friend['id'] ?>;
const url = new URL(`api/v1/user/score/${friend_id}`, document.baseURI);
const container = document.querySelector('#achievements');
const template = document.querySelector('template');

const render = function({ user_score, my_score})
{
    requestJSON('achievements.json')
    .then(json =>
    {
        json.achievements.forEach(item =>
        {
            const clone = template.content.cloneNode(true);
            const friend_progress = clone.querySelector('.friend .progress_bar');
            const my_progress = clone.querySelector('.my .progress_bar');
            const friend_value = user_score[item.goal_type];
            const my_value = my_score[item.goal_type];

            const friend_fill = Math.min(friend_value / item.goal * 100, 100);
            const my_fill = Math.min(my_value / item.goal * 100, 100);

            friend_progress.style.background = `linear-gradient(to right, #358 ${friend_fill}%, transparent ${friend_fill}%)`;
            my_progress.style.background = `linear-gradient(to right, #358 ${my_fill}%, transparent ${my_fill}%)`;

            clone.querySelectorAll('img').forEach(img => img.src = `images/achievements/${item.icon}`);
            clone.querySelector('.title').textContent = item.title;
            clone.querySelector('.description').textContent = item.description;

            if (item.show)
            {
                friend_progress.textContent = `${friend_value}/${item.goal}`;
                my_progress.textContent = `${my_value}/${item.goal}`;
            }

            if (friend_value >= item.goal)
            {
                clone.querySelector('.image.friend').classList.add('unlock');
            }

            if (my_value >= item.goal)
            {
                clone.querySelector('.image.my').classList.add('unlock');
            }

            container.appendChild(clone);
        });
    });
}

requestJSON(url)
.then(json =>
{
    render(json)
})
.catch(error =>
{
    console.error('Erro ao carregar:', error);
});

</script>