function notification(status, message)
{
    const card = document.createElement('div');

    card.innerHTML = message[status]; 
    card.classList.add('notification', status);   
    document.body.insertBefore(card, document.body.firstChild);

    setTimeout(() =>
    {
        card.remove();

    }, 1000 * 3);
}