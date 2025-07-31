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