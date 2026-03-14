document.addEventListener('turbo:load', function() {
    const radioMensuel = document.getElementById('radio-mensuel');
    const radioAnnuel = document.getElementById('radio-annuel');
    const inputDisplay = document.getElementById('input-duree-display');
    const inputHidden = document.getElementById('input-duree-hidden');
    const addonDuree = document.getElementById('addon-duree');

    // Sécurité au cas où on n'est pas sur la bonne page
    if (!radioMensuel || !radioAnnuel || !inputDisplay) return;

    function updateMode() {
        if (radioMensuel.checked) {
            addonDuree.innerText = 'mois';
        } else {
            addonDuree.innerText = 'an(s)';
        }
        updateHidden(); // On déclenche la vérification dès qu'on change de mode
    }

    function updateHidden() {
        let val = parseInt(inputDisplay.value) || 1;
        const isMensuel = radioMensuel.checked;

        // On nettoie les anciennes alertes rouges
        inputDisplay.classList.remove('is-invalid');
        const oldFeedback = inputDisplay.parentNode.querySelector('.invalid-feedback');
        if (oldFeedback) oldFeedback.remove();

        if (isMensuel) {
            if (val > 9) {
                // Création de l'alerte visuelle pour les mois
                inputDisplay.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.innerText = 'Au-delà de 9 mois, passez en annuel !';
                inputDisplay.parentNode.appendChild(feedback);
                val = 9;
            }
            inputHidden.value = val;
        } else {
            if (val > 5) {
                // Création de l'alerte visuelle pour les années
                inputDisplay.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.innerText = 'L\'engagement maximum est de 5 ans.';
                inputDisplay.parentNode.appendChild(feedback);
                val = 5;
            }
            inputHidden.value = val * 12; // On envoie toujours en mois au PHP (ex: 2 ans = 24)
        }
    }

    // Écouteurs d'événements
    radioMensuel.addEventListener('change', updateMode);
    radioAnnuel.addEventListener('change', updateMode);

    // Quand on tape au clavier
    inputDisplay.addEventListener('input', updateHidden);

    // Quand on clique en dehors du champ (pour forcer le changement du chiffre affiché)
    inputDisplay.addEventListener('blur', function() {
        if (radioMensuel.checked && parseInt(inputDisplay.value) > 9) inputDisplay.value = 9;
        if (!radioMensuel.checked && parseInt(inputDisplay.value) > 5) inputDisplay.value = 5;
        if (parseInt(inputDisplay.value) < 1 || isNaN(parseInt(inputDisplay.value))) inputDisplay.value = 1;
    });
    updateMode();
});
