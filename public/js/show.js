document.addEventListener('turbo:load', function() {
    const form = document.getElementById('offer-form');
    if (!form) return;

    const radioMensuel = document.getElementById('radio-mensuel');
    const radioAnnuel = document.getElementById('radio-annuel');
    const inputDisplay = document.getElementById('input-duree-display');
    const inputHidden = document.getElementById('input-duree-hidden');
    const labelDuree = document.getElementById('label-duree');
    const addonDuree = document.getElementById('addon-duree');

    function updateMode() {
        if (radioMensuel.checked) {
            labelDuree.innerText = 'Durée (Mois) :';
            addonDuree.innerText = 'mois';
            inputDisplay.max = 9;
            if (parseInt(inputDisplay.value) > 9) inputDisplay.value = 9;
        } else {
            labelDuree.innerText = 'Durée (Années) :';
            addonDuree.innerText = 'an(s)';
            inputDisplay.max = 5;
            if (parseInt(inputDisplay.value) > 5) inputDisplay.value = 1;
        }
        updateHidden();
    }

    function updateHidden() {
        let val = parseInt(inputDisplay.value) || 1;
        if (radioMensuel.checked) {
            if (val > 9) {
                val = 9;
                inputDisplay.value = 9;
                alert("Au-delà de 9 mois, l'abonnement annuel est beaucoup plus économique !");
            }
            inputHidden.value = val;
        } else {
            if (val > 5) val = 5;
            inputHidden.value = val * 12; // On convertit l'année en mois pour le PHP
        }
    }

    radioMensuel.addEventListener('change', updateMode);
    radioAnnuel.addEventListener('change', updateMode);
    inputDisplay.addEventListener('input', updateHidden);

    form.addEventListener('submit', function(e) {
        updateHidden();
        if (radioMensuel.checked && parseInt(inputHidden.value) > 9) {
            e.preventDefault(); // Bloque l'envoi
            alert('Erreur : La durée mensuelle ne peut pas dépasser 9 mois.');
        }
    });
});
