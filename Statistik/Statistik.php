<?php

class Statistik
{

    /**
     * Berechnet alle Einnahmen
     */
    public function getEinnahmen(array $transaktionen): float
    {
        $summe = 0;


        foreach ($transaktionen as $transaktion) {

            if ($transaktion["typ"] === "Einnahme") {

                $summe += $transaktion["betrag"];

            }

        }


        return $summe;
    }



    /**
     * Berechnet alle Ausgaben
     */
    public function getAusgaben(array $transaktionen): float
    {
        $summe = 0;


        foreach ($transaktionen as $transaktion) {

            if ($transaktion["typ"] === "Ausgabe") {

                $summe += $transaktion["betrag"];

            }

        }


        return $summe;
    }



    /**
     * Berechnet den aktuellen Kontostand
     */
    public function getKontostand(array $transaktionen): float
    {

        return 
            $this->getEinnahmen($transaktionen)
            -
            $this->getAusgaben($transaktionen);

    }




    /**
     * Durchschnittliche Ausgabe
     */
    public function getDurchschnittAusgaben(array $transaktionen): float
    {

        $ausgaben = [];


        foreach ($transaktionen as $transaktion) {

            if ($transaktion["typ"] === "Ausgabe") {

                $ausgaben[] = $transaktion["betrag"];

            }

        }


        if (count($ausgaben) === 0) {
            return 0;
        }


        return array_sum($ausgaben) / count($ausgaben);

    }




    /**
     * Größte Ausgabe finden
     */
    public function getGroessteAusgabe(array $transaktionen): float
    {

        $max = 0;


        foreach ($transaktionen as $transaktion) {

            if (
                $transaktion["typ"] === "Ausgabe"
                &&
                $transaktion["betrag"] > $max
            ) {

                $max = $transaktion["betrag"];

            }

        }


        return $max;

    }




    /**
     * Größte Einnahme finden
     */
    public function getGroessteEinnahme(array $transaktionen): float
    {

        $max = 0;


        foreach ($transaktionen as $transaktion) {

            if (
                $transaktion["typ"] === "Einnahme"
                &&
                $transaktion["betrag"] > $max
            ) {

                $max = $transaktion["betrag"];

            }

        }


        return $max;

    }




    /**
     * Ausgaben nach Thema gruppieren
     *
     * Rückgabe:
     *
     * [
     *   "Miete" => 700,
     *   "Lebensmittel" => 300
     * ]
     */
    public function getAusgabenNachThema(array $transaktionen): array
    {

        $ergebnis = [];


        foreach ($transaktionen as $transaktion) {


            if ($transaktion["typ"] === "Ausgabe") {


                $thema = $transaktion["thema"];


                if (!isset($ergebnis[$thema])) {

                    $ergebnis[$thema] = 0;

                }


                $ergebnis[$thema] += $transaktion["betrag"];

            }

        }


        return $ergebnis;

    }





    /**
     * Monatliche Ausgaben
     *
     * Rückgabe:
     *
     * [
     * "Januar" => 500,
     * "Februar" => 700
     * ]
     */
    public function getMonatsAusgaben(array $transaktionen): array
    {

        $monate = [];


        foreach ($transaktionen as $transaktion) {


            if ($transaktion["typ"] === "Ausgabe") {


                // Monatsnummer (1-12) statt Monatsname,
                // date("F") liefert immer englische Namen (z.B. "July")
                $monatNummer = (int) date(
                    "n",
                    strtotime($transaktion["datum"])
                );


                if (!isset($monate[$monatNummer])) {

                    $monate[$monatNummer] = 0;

                }


                $monate[$monatNummer] += $transaktion["betrag"];

            }

        }


        return $monate;

    }



}