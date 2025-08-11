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
                <span></span>
            </div>
            <progress></progress>
        </div>
    </div>
</template>

<script>

const friend_id = '<?= $friend_id ?>';
const url = new URL(`api/v1/user/score/${friend_id}`, document.baseURI);
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
            const progress = clone.querySelector('progress');

            progress.max = item.goal;
            progress.value = score[item.goal_type];
            clone.querySelector('img').src = `images/achievements/${item.icon}`;
            clone.querySelector('.title').textContent = item.title;
            clone.querySelector('.description').textContent = item.description;

            if (item.show)
            {
                clone.querySelector('span').textContent = `${score[item.goal_type]}/${item.goal}`;
            }

            if (score[item.goal_type] >= item.goal)
            {
                clone.querySelector('.item').classList.add('unlock');
            }

            container.appendChild(clone);
        });
    });
}

getJSON();

</script>