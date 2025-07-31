<div class="user_panel">
    <?php if ($session->logged_in): ?>
    <div class="steam_card connected">
        <div class="avatar">
            <img alt="" src="https://avatars.steamstatic.com/<?= $session->avatarhash ?>.jpg" />
        </div>
        <div class="personaname"><?= $session->personaname ?></div>
    </div>
    <a class="logout" href="logout">Sair</a>
    <?php else: ?>
    <a class="steam_card connect" href="auth?login" title="Entrar com a Steam">
        <span>Entrar com a</span>
        <img alt="" src="logo_steam.svg" />
    </a>
    <?php endif ?>
</div>