<?php $this->insert('components/my_user_panel.php') ?>
<div id="achievements" class="flex_row"></div>
<template>
    <div class="flex_row">
        <div class="item flex_row" data-value="1">
            <div class="flex_column">
                <div class="flex_row">
                    <img />
                </div>
                <div class="flex_row flex">
                    <div class="title"></div>
                    <div class="description"></div>
                </div>
            </div>
            <div class="progress">
                <div class="progress_bar"></div>
            </div>
        </div>
    </div>
</template>

<script>

const url = new URL('api/v1/score', document.baseURI);
const container = document.querySelector('#achievements');
const template = document.querySelector('template');

const render = function(score)
{
    requestJSON('achievements.json')
    .then(json =>
    {
        json.achievements.forEach(item =>
        {
            const clone = template.content.cloneNode(true);
            const progress = clone.querySelector('.progress_bar');
            const value = score[item.goal_type];
            const fill = Math.min(value / item.goal * 100, 100);

            progress.style.background = `linear-gradient(to right, #358 ${fill}%, transparent ${fill}%)`;
            clone.querySelector('img').src = `images/achievements/${item.icon}`;
            clone.querySelector('.title').textContent = item.title;
            clone.querySelector('.description').textContent = item.description;

            if (item.show)
            {
                progress.textContent = `${value}/${item.goal}`;
            }

            if (value >= item.goal)
            {
                clone.querySelector('.item').classList.add('unlock');
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