let refreshTimer;

function refreshCsrf() {
    fetch('/refresh-csrf', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const csrfInput = document.querySelector('input[name="_token"]');
        if (csrfInput) {
            csrfInput.value = data.csrf;
            console.log('Token CSRF atualizado.');
        }
    })
    .catch(error => {
        console.error('Erro ao renovar token CSRF:', error);
    });
}

function startRefreshTimer() {
    clearInterval(refreshTimer);
    refreshTimer = setInterval(refreshCsrf, 5 * 60 * 1000); 
    refreshCsrf(); 
}

['click', 'keydown', 'mousemove', 'scroll'].forEach(event =>
    document.addEventListener(event, startRefreshTimer)
);

startRefreshTimer();
