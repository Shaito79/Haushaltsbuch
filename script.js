
const jahrSelect = document.getElementById("jahr");
const aktuellesJahr = new Date().getFullYear();

for (let jahr = aktuellesJahr - 5; jahr <= aktuellesJahr + 5; jahr++) {
    const option = document.createElement("option");

    option.value = jahr;
    option.textContent = jahr;

    if (jahr === aktuellesJahr) {
        option.selected = true;
    }

    jahrSelect.appendChild(option);
}
