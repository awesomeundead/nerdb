const session = {};

async function login()
{
    try
    {
        const response = await fetch('login.php');

        if (!response.ok)
        {
            throw new Error(response.statusText);
        }

        const json = await response.json();

        if (json)
        {
            session.steamid = json.steamid;
            session.personaname = json.personaname;

            return true;
        }
        else
        {
            return false;
        }
    }
    catch(error)
    {
        console.error(error);
    }
}

async function check_login()
{
    const logged_in = await login();

    if (!logged_in)
    {
        window.location.href = 'index.html';
    }
}