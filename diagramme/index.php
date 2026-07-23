<?php

// -------------------------------
// Testdaten
// Später kommen diese aus SQL
// -------------------------------

require "../Statistik/Statistik.php";
require "../Statistik/testdaten.php";

/** @var array $transaktionen */

echo "<pre>";

var_dump(isset($transaktionen));

var_dump($transaktionen);

echo "</pre>";

$statistik = new Statistik();

$einnahmen = $statistik->getEinnahmen($transaktionen);
$ausgaben = $statistik->getAusgaben($transaktionen);
$ausgabenNachThema = $statistik->getAusgabenNachThema($transaktionen);
$monatsAusgaben = $statistik->getMonatsAusgaben($transaktionen);


$monatsNamen = [
    1 => "Januar",
    2 => "Februar",
    3 => "März",
    4 => "April",
    5 => "Mai",
    6 => "Juni",
    7 => "Juli",
    8 => "August",
    9 => "September",
    10 => "Oktober",
    11 => "November",
    12 => "Dezember"
];

$monate = array_values($monatsNamen);

// Für jeden Monat den passenden Wert aus $monatsAusgaben holen,
// 0 falls in dem Monat nichts ausgegeben wurde.
$monatsAusgabenWerte = [];
foreach ($monatsNamen as $nummer => $name) {
    $monatsAusgabenWerte[] = $monatsAusgaben[$nummer] ?? 0;
}


?>

<!DOCTYPE html>
<html lang="de">

<head>

<meta charset="UTF-8">
<title>Haushaltsbuch - Diagramme</title>

<!-- Bootstrap optional für Layout -->
<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart.js Plugin für Datenbeschriftungen (Prozentangaben im Kuchendiagramm) -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


</head>


<body>


<div class="container mt-5">


<h1 class="mb-4">
    Diagramme
</h1>



<div class="row">


<!-- Einnahmen / Ausgaben -->

<div class="col-md-6">

<div class="card">

<div class="card-header">
Einnahmen vs. Ausgaben
</div>


<div class="card-body">

<canvas id="einnahmenAusgaben"></canvas>

</div>

</div>

</div>



<!-- Ausgaben Themen -->

<div class="col-md-6">

<div class="card">

<div class="card-header">
Ausgaben nach Thema
</div>


<div class="card-body">

<canvas id="ausgabenThemen"></canvas>

</div>

</div>

</div>


</div>




<div class="row mt-4">


<div class="col-md-12">

<div class="card">

<div class="card-header">
Ausgaben Entwicklung
</div>


<div class="card-body">

<canvas id="monatsVerlauf"></canvas>

</div>

</div>


</div>


</div>


</div>



<script>


// ------------------------------------
// Diagramm 1
// Einnahmen vs. Ausgaben
// ------------------------------------

new Chart(
    document.getElementById('einnahmenAusgaben'),

    {

    type: 'bar',

    data:
    {

        labels:
        [
            'Einnahmen',
            'Ausgaben'
        ],


        datasets:
        [
            {

            label: 'Euro',

            data:
            [
                <?= $einnahmen ?>,
                <?= $ausgaben ?>
            ],

            backgroundColor:
            [
                'darkgreen',
                'darkred'
            ]

            }
        ]

    },

    options:
    {

        plugins:
        {

            legend:
            {
                display: false
            },

            tooltip:
            {

                callbacks:
                {
                    label: function(context)
                    {
                        return context.parsed.y + ' €';
                    }
                }

            }

        },

        scales:
        {

            y:
            {

                ticks:
                {
                    callback: function(value)
                    {
                        return value + ' €';
                    }
                }

            }

        }

    }

});




// ------------------------------------
// Diagramm 2
// Ausgaben nach Thema
// ------------------------------------

new Chart(

document.getElementById('ausgabenThemen'),

{

type: 'pie',


data:
{

labels:
<?= json_encode(array_keys($ausgabenNachThema)) ?>,


datasets:
[

{

label:'Ausgaben',

data:
<?= json_encode(array_values($ausgabenNachThema)) ?>

}

]

},

plugins:
[
    ChartDataLabels
],

options:
{

plugins:
{

    datalabels:
    {

        color: 'black',

        font:
        {
            weight: 'bold'
        },

        formatter: function(value, context)
        {
            const werte = context.chart.data.datasets[0].data;
            const summe = werte.reduce((a, b) => a + b, 0);
            const prozent = (value / summe * 100).toFixed(1);
            return prozent + '%';
        }

    }

}

}

}

);




// ------------------------------------
// Diagramm 3
// Monatsentwicklung
// ------------------------------------


new Chart(

document.getElementById('monatsVerlauf'),

{

type:'line',


data:
{

labels:
<?= json_encode($monate) ?>,


datasets:
[

{

label:'Ausgaben pro Monat',

data:
<?= json_encode($monatsAusgabenWerte) ?>

}

]

},

options:
{

    scales:
    {

        y:
        {

            ticks:
            {
                callback: function(value)
                {
                    return value + ' €';
                }
            }

        }

    }

}

}

);



</script>


</body>

</html>