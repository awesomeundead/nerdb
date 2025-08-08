<div class="user_panel">
    <?php if ($session->logged_in): ?>
    <div id="menu_mobile">
        <label>
            <input type="checkbox" />
        </label>
    </div>
    <nav>
        <a href="mylist/movies">Minha lista de filmes</a>
        <a href="mylist/games">Minha lista de jogos</a>
        <a href="friends">Amigos</a>
        <a href="movie/add">Adicionar filme</a>
        <a href="game/add">Adicionar jogo</a>
    </nav>
    <div class="steam_connected">
        <div class="avatar">
            <img alt="Imagem do seu avatar" src="https://avatars.steamstatic.com/<?= $session->avatarhash ?>.jpg" />
        </div>
        <div class="personaname"><?= $session->personaname ?></div>
    </div>
    <a class="logout" href="logout">Sair</a>
    <?php else: ?>
    <a class="steam_disconnected" href="auth?login" title="Entrar com a Steam">
        <span>Entrar com a</span>
        <img alt="Link para se conectar via Steam" src="logo_steam.svg" />
    </a>
    <?php endif ?>
</div>