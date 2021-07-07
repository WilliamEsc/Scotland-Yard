-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 17 sep. 2019 à 15:40
-- Version du serveur :  10.1.38-MariaDB
-- Version de PHP :  7.3.4

/* Dropping existing tables */

DROP TABLE IF EXISTS `projet__Joueuse`;
DROP TABLE IF EXISTS `projet__Partie`;
DROP TABLE IF EXISTS `projet__Image`;
DROP TABLE IF EXISTS `projet__Configuration`;
DROP TABLE IF EXISTS `projet__Quartier`;
DROP TABLE IF EXISTS `projet__Route`;
DROP TABLE IF EXISTS `projet__MisterX`;
DROP TABLE IF EXISTS `projet__Communes`;
DROP TABLE IF EXISTS `projet__Departement`;


/* Creating tables */ 

--
-- Structure de la table `Joueuse`
--

CREATE TABLE `projet__Joueuse` (
	`nomJ` VARCHAR(255) DEFAULT NULL,
	`emailJ` VARCHAR(255) DEFAULT NULL,
	`nbV` INTEGER DEFAULT NULL,
	PRIMARY KEY(nomJ, emailJ)
);

--
-- Structure de la table `Partie`
--

CREATE TABLE `projet__Partie` (
	`dateDemarrage` DATETIME,
	`nbDetective` INTEGER DEFAULT NULL,
	`nomJ` VARCHAR(255) REFERENCES projet__Joueuse(nomJ),
	`nomCf` VARCHAR(255) REFERENCES projet__Configuration(nomCf),
	PRIMARY KEY(dateDemarrage,nomJ)
);

--
-- Structure de la table `Image`
--

CREATE TABLE `projet__Image` (
	`nomI` VARCHAR(255) DEFAULT NULL,
	`cheminI` VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY(nomI,cheminI)
);

--
-- Structure de la table `Configuration`
--

CREATE TABLE `projet__Configuration` (
	`nomCf` VARCHAR(255) DEFAULT NULL,
	`dateCf` DATE,
	`imageMetro` VARCHAR(255) REFERENCES projet__Image(nomI),
	`imageTaxi` VARCHAR(255) REFERENCES projet__Image(nomI),
	`imageBus` VARCHAR(255) REFERENCES projet__Image(nomI),
	`imageNoir` VARCHAR(255) REFERENCES projet__Image(nomI),
	PRIMARY KEY(nomCf)
);

--
-- Structure de la table `Quartier`
--

CREATE TABLE `projet__Quartier` (
	`idQ` INTEGER DEFAULT NULL,
	`nomQ` VARCHAR(255) DEFAULT NULL,
	`typeQ` VARCHAR(1) DEFAULT NULL,
	`longitude` VARCHAR(1463) DEFAULT NULL,
	`latitude` VARCHAR(1463) DEFAULT NULL,
	`ptsDepart` INTEGER(1) DEFAULT NULL,
	`NomCo` VARCHAR(255) REFERENCES projet__Communes(NomCo), 
	PRIMARY KEY(idQ)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Structure de la table `Route`
--

CREATE TABLE `projet__Route` (
	`TypeTransport` VARCHAR(13) DEFAULT NULL,
	`idQ1` INTEGER REFERENCES projet__Quartier(idQ),
	`idQ2` INTEGER REFERENCES projet__Quartier(idQ),
	PRIMARY KEY (TypeTransport,idQ1,idQ2)
);

--
-- Structure de la table `MisterX`
--

CREATE TABLE `projet__MisterX` (
	`DateDemarrage` DATETIME REFERENCES projet__Partie(DateDemarrage),
	`NumTour` INTEGER DEFAULT NULL,
	`idQ1` INTEGER REFERENCES projet__Route(idQ1),
	`idQ2` INTEGER REFERENCES projet__Route(idQ2),
	`TypeTransport` VARCHAR(13) REFERENCES projet__Route(TypeTransport),
	PRIMARY KEY(DateDemarrage,NumTour)
);

--
-- Structure de la table `Communes`
--

CREATE TABLE `projet__Communes` (
	`NomCo` VARCHAR(255) DEFAULT NULL, 
	`CodePostal` INTEGER DEFAULT NULL,
	`idD` VARCHAR(255) REFERENCES projet__Departement(idD),
	PRIMARY KEY(NomCo)
);

--
-- Structure de la table `Departement`
--

CREATE TABLE `projet__Departement` (
	`idD` VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY(idD)
);

/* Inserting instances */ 

INSERT INTO projet__Configuration VALUES ('basique','2019-12-11','ticketMetro','ticketTaxi','ticketBus','ticketNoir');
INSERT INTO projet__Configuration VALUES ('econome','2019-12-11','ticketMetro','ticketTaxi','ticketBus','ticketNoir');
INSERT INTO projet__Configuration VALUES ('pistage','2019-12-11','ticketMetro','ticketTaxi','ticketBus','ticketNoir');
INSERT INTO projet__Image VALUES ('ticketMetro','./DATA/pion_under.jpg');
INSERT INTO projet__Image VALUES ('ticketTaxi','./DATA/pion_taxi.jpg');
INSERT INTO projet__Image VALUES ('ticketBus','./DATA/pion_bus.jpg');
INSERT INTO projet__Image VALUES ('ticketNoir','./DATA/pion_black.jpg');
INSERT INTO projet__Joueuse VALUES ('MisterX','incognito@hotmail.fr','1');
INSERT INTO projet__Joueuse VALUES ('William','william@gmail.com','1');
INSERT INTO projet__Joueuse VALUES ('Antoine','antoine@gmail.com','1');
INSERT INTO projet__Partie VALUES ('2019-12-11 00:00:00','4','MisterX','pistage');
INSERT INTO projet__Partie VALUES ('2019-12-11 00:00:01','4','William','econome');
INSERT INTO projet__Partie VALUES ('2019-12-11 00:00:02','4','Antoine','basique');






      



