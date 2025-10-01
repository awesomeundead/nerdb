<div id="game_form">
    <h1>Atualizar jogo</h1>
    <div class="grid">
        <div class="image">
            <?php if ($game['media']): ?>
            <img alt="Capa do filme" src="images/games/512/<?= $game['media'] ?>.webp" />
            <?php else: ?>
            <img alt="Sem imagem" src="noimage.png" />
            <?php endif ?>
        </div>
        <form id="update_game">
            <label>Título</label>
            <input name="title" placeholder="Título" required="required" type="text" value="<?= $game['title'] ?>" />
            <label>Desenvolvedor(es)*</label>
            <input name="developer" placeholder="Desenvolvedor" required="required" type="text" value="<?= $game['developer'] ?>" />
            <label>Gênero(s)*</label>
            <input name="genres" placeholder="Gêneros" type="text" value="<?= $game['genres'] ?>" />
            <div class="advice">* Use ";" para mais de um desenvolvedor ou gênero, exemplo: Ação;Aventura;Fantasia</div>
            <label>Ano de lançamento</label>
            <input name="release_year" placeholder="Ano de lançamento" required="required" type="number" value="<?= $game['release_year'] ?>" />
            <label>Link para a Steam</label>
            <input name="steam" placeholder="Link Steam" type="text" value="<?= $game['steam'] ?>" />
            <button type="submit">Atualizar</button>
        </form>
    </div>
</div>


<script>

const game_id = <?= $game['id'] ?>;
const url = new URL(`api/v1/game/${game_id}`, document.baseURI);
const form = document.querySelector('#update_game');

updateForm(url, form);

</script>