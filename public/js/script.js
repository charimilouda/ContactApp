document.getElementById('search-bar').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const contactCards = document.querySelectorAll('.card');

    contactCards.forEach(card => {
        const name = card.querySelector('h5').textContent.toLowerCase();
        if (name.startsWith(query)) {
            card.style.display = 'block'; // Affiche les cartes correspondant à la recherche
        } else {
            card.style.display = 'none'; // Masque les autres cartes
        }
    });
});

function confirmDelete() {
    return confirm("Êtes-vous sûr de vouloir supprimer ce contact ?");
}