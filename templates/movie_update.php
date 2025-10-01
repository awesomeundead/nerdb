<div id="movie_form">
    <h1>Atualizar filme</h1>
    <div class="grid">
        <div class="image">
            <?php if ($movie['media']): ?>
            <img alt="Capa do filme" src="images/512/<?= $movie['media'] ?>.webp" />
            <?php else: ?>
            <img alt="Sem imagem" src="noimage.png" />
            <?php endif ?>
        </div>
        <form id="update_movie">
            <label>Título em português</label>
            <input name="title_br" placeholder="Título em português" required="required" type="text" value="<?= $movie['title_br'] ?>" />
            <label>Título em inglês</label>
            <input name="title_us" placeholder="Título em inglês" required="required" type="text" value="<?= $movie['title_us'] ?>" />
            <label>Diretor(es)*</label>
            <input name="director" list="directors" placeholder="Diretor" required="required" type="text" value="<?= $movie['director'] ?>" />
            <label>Gênero(s)*</label>
            <input name="genres" placeholder="Gêneros" type="text" value="<?= $movie['genres'] ?>" />
            <div class="advice">* Use ";" para mais de um diretor ou gênero, exemplo: Drama;Mistério;Suspense</div>
            <label>Ano de lançamento</label>
            <input name="release_year" placeholder="Ano de lançamento" required="required" type="number" value="<?= $movie['release_year'] ?>" />
            <label>Link para o IMDB</label>
            <input name="imdb" placeholder="Link IMDB" type="text" value="<?= $movie['imdb'] ?>" />
            <button type="submit">Atualizar</button>
            <button id="button_dialog_open" type="button">Elenco</button>
        </form>
    </div>
    <dialog id="update_cast">
        <form>
            <label>Elenco</label>
            <textarea name="cast" rows="16"></textarea>
            <button type="submit">Atualizar</button>
            <button id="button_dialog_close" type="button">Fechar</button>
        </form>
    </dialog>
</div>


<script>

const movie_id = <?= $movie['id'] ?>;
const url = new URL(`api/v1/movie/${movie_id}`, document.baseURI);
const form = document.querySelector('#update_movie');
const dialog = document.querySelector('#update_cast');
const form_cast = document.querySelector('#update_cast form');

document.querySelector('#button_dialog_open').addEventListener('click', () => dialog.showModal());
document.querySelector('#button_dialog_close').addEventListener('click', () => dialog.close());

updateForm(url, form);
updateForm(new URL(`api/v1/movie/${movie_id}/cast`, document.baseURI), form_cast);
get_directors();

</script>