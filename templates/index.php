<link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet" />
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<div id="home">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper" id="movies_media"></div>
    </div>
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
const url = new URL('api/v1/movies', document.baseURI);

const render = function({ movies })
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

url.searchParams.set('order', 'random');
getJSON();

</script>