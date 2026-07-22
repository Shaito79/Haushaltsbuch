-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 22. Jul 2026 um 14:10
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `haushaltsbuch`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `konten`
--

CREATE TABLE `konten` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `kontostand` decimal(10,2) NOT NULL DEFAULT 0.00,
  `erstellt_am` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `konten`
--

INSERT INTO `konten` (`id`, `name`, `kontostand`, `erstellt_am`) VALUES
(1, 'Girokonto', 1000.00, '2026-07-21 14:03:11');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `themen`
--

CREATE TABLE `themen` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `typ` enum('einnahme','ausgabe') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `themen`
--

INSERT INTO `themen` (`id`, `name`, `typ`) VALUES
(1, 'Gehalt', 'einnahme'),
(2, 'Lebensmittel', 'ausgabe'),
(3, 'Miete', 'ausgabe'),
(4, 'Freizeit', 'ausgabe');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transaktionen`
--

CREATE TABLE `transaktionen` (
  `id` int(11) NOT NULL,
  `konto_id` int(11) NOT NULL,
  `thema_id` int(11) NOT NULL,
  `betrag` decimal(10,2) NOT NULL,
  `beschreibung` varchar(255) DEFAULT NULL,
  `datum` date NOT NULL,
  `erstellt_am` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `transaktionen`
--

INSERT INTO `transaktionen` (`id`, `konto_id`, `thema_id`, `betrag`, `beschreibung`, `datum`, `erstellt_am`) VALUES
(1, 1, 2, 50.00, 'Einkauf', '2026-07-21', '2026-07-21 14:13:07'),
(2, 1, 4, -75.00, 'Ausflug', '2026-07-22', '2026-07-22 08:45:44'),
(3, 1, 3, 500.00, 'Mieter1', '2026-07-22', '2026-07-22 08:53:36');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transaktionen_tags`
--

CREATE TABLE `transaktionen_tags` (
  `transaktion_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `konten`
--
ALTER TABLE `konten`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indizes für die Tabelle `themen`
--
ALTER TABLE `themen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `transaktionen`
--
ALTER TABLE `transaktionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `konto_id` (`konto_id`),
  ADD KEY `thema_id` (`thema_id`);

--
-- Indizes für die Tabelle `transaktionen_tags`
--
ALTER TABLE `transaktionen_tags`
  ADD PRIMARY KEY (`transaktion_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `konten`
--
ALTER TABLE `konten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `themen`
--
ALTER TABLE `themen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `transaktionen`
--
ALTER TABLE `transaktionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `transaktionen`
--
ALTER TABLE `transaktionen`
  ADD CONSTRAINT `transaktionen_ibfk_1` FOREIGN KEY (`konto_id`) REFERENCES `konten` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaktionen_ibfk_2` FOREIGN KEY (`thema_id`) REFERENCES `themen` (`id`);

--
-- Constraints der Tabelle `transaktionen_tags`
--
ALTER TABLE `transaktionen_tags`
  ADD CONSTRAINT `transaktionen_tags_ibfk_1` FOREIGN KEY (`transaktion_id`) REFERENCES `transaktionen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaktionen_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
