// Meldungen nach einigen Sekunden dezent ausblenden.
document.querySelectorAll('.alert').forEach((element) => {
    window.setTimeout(() => {
        element.style.transition = 'opacity .35s ease';
        element.style.opacity = '0';
        window.setTimeout(() => element.remove(), 350);
    }, 5000);
});
