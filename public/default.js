function notification(status, message)
{
    const card = document.createElement('div');
    const container = document.querySelector('body');

    card.innerHTML = message[status]; 
    card.classList.add('notification', status);   
    container.insertBefore(card, container.firstChild);

    setTimeout(() =>
    {
        card.remove();

    }, 1000 * 3);
}

function load_movies(url, render)
{
    fetch(url)
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
        render(json.movies)
    })
    .catch(error =>
    {
        console.error(error);
    });
}