<div id="friend">
    <div class="image">
        <img alt="" src="https://avatars.steamstatic.com/<?= $user['avatarhash'] ?>_full.jpg" />
    </div>
    <div class="content">
        <div class="personaname"><?= $user['personaname'] ?></div>
        <div class="role"><?= $user['role'] ?></div>
        <div class="created_date"><?= $user['created_date'] ?></div>
        <nav>
            <a class="nolink" data-namelist="movielist" href="my/movielist">Filmes</a>
            <a class="nolink" data-namelist="gamelist" href="my/gamelist">Jogos</a>
            <a href="my/friends">Amigos</a>
            <a href="my/achievements">Conquistas</a>
        </nav>
    </div>
</div>