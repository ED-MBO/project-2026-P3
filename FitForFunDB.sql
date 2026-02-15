SET sql_notes = 0;



/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Database schema voor FitForFun, een sportschool die groepslessen aanbiedt. 
                De database bevat tabellen voor gebruikers, medewerkers, leden, lessen en reserveringen.
 
*/
DROP DATABASE IF EXISTS FitForFunDB;
CREATE DATABASE FitForFunDB;

USE FitForFunDB;



/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de gebruikers aangemaakt. Deze tabel bevat informatie over de gebruikers van het systeem, zoals hun naam, gebruikersnaam, wachtwoord en inlogstatus.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/



CREATE TABLE gebruiker (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,Voornaam VARCHAR(50) NOT NULL
    ,Tussenvoegsel VARCHAR(10)
    ,Achternaam VARCHAR(50) NOT NULL
    ,Gebruikersnaam VARCHAR(100) NOT NULL UNIQUE
    ,Wachtwoord VARCHAR(255) NOT NULL
    ,IsIngelogd TINYINT(1) NOT NULL DEFAULT 0
    ,Ingelogd DATE NULL
    ,Uitgelogd DATE NULL
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250) NULL
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
)ENGINE=InnoDB;




/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de rollen aangemaakt. Deze tabel bevat informatie over de verschillende rollen die gebruikers kunnen hebben binnen het systeem, zoals hun naam en of ze actief zijn.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/


CREATE TABLE rol (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,GebruikerId INT NOT NULL
    ,Naam VARCHAR(100) NOT NULL
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250)
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
    ,FOREIGN KEY (GebruikerId) REFERENCES gebruiker(Id) ON DELETE CASCADE
)ENGINE=InnoDB;



/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de medewerkers aangemaakt. Deze tabel bevat informatie over de medewerkers van de sportschool, zoals hun naam, nummer, soort medewerker en of ze actief zijn.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/


CREATE TABLE medewerker (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,Voornaam VARCHAR(50) NOT NULL
    ,Tussenvoegsel VARCHAR(10) NULL
    ,Achternaam VARCHAR(50) NOT NULL
    ,Nummer MEDIUMINT NOT NULL
    ,Medewerkersoort VARCHAR(20) NOT NULL
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250) NULL
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
)ENGINE=InnoDB;




/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de leden aangemaakt. Deze tabel bevat informatie over de leden van de sportschool, zoals hun naam, relatienummer, mobiel nummer, email en of ze actief zijn.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/


CREATE TABLE lid (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,Voornaam VARCHAR(50) NOT NULL
    ,Tussenvoegsel VARCHAR(10)
    ,Achternaam VARCHAR(50) NOT NULL
    ,Relatienummer MEDIUMINT NOT NULL
    ,Mobiel VARCHAR(20) NOT NULL
    ,Email VARCHAR(100) NOT NULL UNIQUE
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250) NULL
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
)ENGINE=InnoDB;





/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de lessen aangemaakt. Deze tabel bevat informatie over de groepslessen die worden aangeboden door de sportschool, zoals de naam van de les, prijs, datum, tijd, minimum en maximum aantal personen, beschikbaarheid en of de les actief is.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/


CREATE TABLE les (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,Naam VARCHAR(50) NOT NULL
    ,Prijs DECIMAL(5,2) NOT NULL
    ,Datum DATE NOT NULL
    ,Tijd TIME NOT NULL
    ,MinAantalPersonen TINYINT NOT NULL DEFAULT 3
    ,MaxAantalPersonen TINYINT NOT NULL DEFAULT 9
    ,Beschikbaarheid ENUM('Ingepland','Niet gestart','Gestart','Geannuleerd')
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250) NULL
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
)ENGINE=InnoDB;



/*
  Auteur       : Kene John
  Datum        : 2026-02-15
  Beschrijving : Tabel voor de reserveringen aangemaakt. Deze tabel bevat informatie over de reserveringen die leden maken voor groepslessen, zoals de naam van de reservering, datum, tijd, status van de reservering en of deze actief is.
  Opmerkingen  : Eventuele extra info of opmerkingen
*/


CREATE TABLE reservering (
    Id INT AUTO_INCREMENT PRIMARY KEY
    ,Voornaam VARCHAR(50) NOT NULL
    ,Tussenvoegsel VARCHAR(10)
    ,Achternaam VARCHAR(50) NOT NULL
    ,Nummer MEDIUMINT NOT NULL
    ,Datum DATE NOT NULL
    ,Tijd TIME NOT NULL
    ,Reserveringstatus VARCHAR(20) NOT NULL
    ,Isactief TINYINT(1) NOT NULL DEFAULT 1
    ,Opmerking VARCHAR(250) NULL
    ,Datumaangemaakt DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ,Datumgewijzigd DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
)ENGINE=InnoDB;



SET sql_notes = 1;
