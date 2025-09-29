<div class="steam_connected">
    <div class="avatar">
        <img alt="Imagem do seu avatar" src="https://avatars.steamstatic.com/<?= $session->avatarhash ?>.jpg" />
    </div>
    <div class="personaname"><?= $session->personaname ?></div>
</div>
<button id="menu-toggle" type="button"></button>
<nav class="hidden" id="menu">
    <div class="content">
        <a href="my/movielist">Minha lista</a>
        <a href="my/friends">Amigos</a>
        <a href="my/achievements">Conquistas</a>
        <a href="movie/add">Adicionar filme</a>
        <a href="game/add">Adicionar jogo</a>
        <a href="movielist/added">Filmes adicionados</a>
        <a href="gamelist/added">Jogos adicionados</a>
        <a class="logout" href="logout">Finalizar sessão</a>
    </div>
</nav>
<script>

const toggleBtn = document.getElementById('menu-toggle');
const menu = document.getElementById('menu');

toggleBtn.addEventListener('click', e =>
{
    e.stopPropagation();
    menu.classList.toggle('hidden');
});

document.addEventListener('click', e =>
{
    if (!menu.contains(e.target) && !toggleBtn.contains(e.target))
    {
        menu.classList.add('hidden');
    }
});

</script>