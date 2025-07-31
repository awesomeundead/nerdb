<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Início</title>
<link href="<?= $this->base($this->asset('/layout.css')) ?>" rel="stylesheet" />
<link href="<?= $this->base($this->asset('/default.css')) ?>" rel="stylesheet" />
<link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet" />
<link href="<?= $this->base($this->asset('/index.css')) ?>" rel="stylesheet" />
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
</head>
<body>

<div id="app">
    <header>
        <?php $this->insert('components/user_panel') ?>
        <div id="menu_mobile">
            <label>
                <input type="checkbox" />
            </label>
        </div>
        <?php $this->insert('components/nav') ?>
    </header>
    <section>
        <form action="movies" id="search">
            <input name="q" placeholder="Pesquisar por título, diretor ou ano de lançamento" required="required" type="search" />
            <button type="submit">Pesquisar</button>
        </form>
    </section>
    <footer>
        <div class="swiper mySwiper">
            <div class="swiper-wrapper" id="movies_media"></div>
        </div>
    </footer>
</div>

<template>
    <div class="swiper-slide">
        <a href="">
            <img alt="" loading="lazy" src="" />
        </a>
    </div>
</template>

<script>

const container = document.querySelector('#movies_media');
const template = document.querySelector('template');

load_movies();

function load_movies()
{
    fetch('api/v1/movies?order=random')
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
        render_movies(json.movies)
    })
    .catch(error =>
    {
        console.error(error);
    });
}

function render_movies(movies)
{
    container.innerHTML = '';

    movies.forEach(item =>
    {
        const clone = template.content.cloneNode(true);

        clone.querySelector('a').href = `movie/${item.id}`;
        clone.querySelector('img').alt = `Cartaz do filme ${item.title_br}`;
        clone.querySelector('img').src = `images/512/${item.media}.webp`;

        container.appendChild(clone);
    });

    const swiper = new Swiper('.mySwiper',
    {
        autoplay:
        {
            delay: 1000,
            disableOnInteraction: false,
        },
        slidesPerView: 'auto',
        spaceBetween: 5,
        freeMode: true
    });
}

</script>

</body>
</html>