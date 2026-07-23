<?php

require "Statistik.php";


$transaktionen = [

    [
        "datum"=>"2026-07-01",
        "typ"=>"Einnahme",
        "betrag"=>2500,
        "thema"=>"Gehalt"
    ],

    [
        "datum"=>"2026-07-05",
        "typ"=>"Ausgabe",
        "betrag"=>700,
        "thema"=>"Miete"
    ],

    [
        "datum"=>"2026-07-10",
        "typ"=>"Ausgabe",
        "betrag"=>200,
        "thema"=>"Lebensmittel"
    ]

];


$statistik = new Statistik();


echo "Einnahmen: ";
echo $statistik->getEinnahmen($transaktionen);


echo "<br>";


echo "Ausgaben: ";
echo $statistik->getAusgaben($transaktionen);


echo "<br>";


echo "Kontostand: ";
echo $statistik->getKontostand($transaktionen);


echo "<br>";


echo "<pre>";
print_r(
    $statistik->getAusgabenNachThema($transaktionen)
);
echo "</pre>";