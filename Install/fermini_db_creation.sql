SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- User `www-data`
-- -----------------------------------------------------
CREATE USER 'www-data'@'localhost' IDENTIFIED BY 'password';

-- -----------------------------------------------------
-- Database `Fermini`
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `Fermini` ;
CREATE SCHEMA IF NOT EXISTS `Fermini` ;
GRANT SELECT, INSERT, UPDATE ON `Fermini`.* TO 'www-data'@'localhost';
FLUSH PRIVILEGES;

-- -----------------------------------------------------
-- Table `Fermini`.`Utilisateur`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Utilisateur` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Utilisateur` (
  `idUtilisateur` VARCHAR(15) NOT NULL ,
  `nom` VARCHAR(15) NOT NULL COMMENT 'Nom de l\'utilisateur' ,
  `prenom` VARCHAR(15) NOT NULL ,
  `motPasse` CHAR(40) NOT NULL COMMENT 'hash SHA1(password utilisateur)' ,
  `typeUtilisateur` VARCHAR(10) NOT NULL DEFAULT 'user' ,
  PRIMARY KEY (`idUtilisateur`) )
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`Souche`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Souche` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Souche` (
  `idSouche` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nomLabo` VARCHAR(25) NOT NULL COMMENT 'Nom d\'usage au laboratoire' ,
  `numMTF` SMALLINT(5) UNSIGNED NULL COMMENT 'Numero MTF dans la collection du labo' ,
  `nomScientifique` VARCHAR(50) NULL COMMENT 'Genre et espece' ,
  `description` VARCHAR(255) NULL COMMENT 'Description optionelle de la souche' ,
  PRIMARY KEY (`idSouche`) )
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`Prelevement`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Prelevement` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Prelevement` (
  `idPrelevement` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Numero permettant l\'identification de l\'echantillon' ,
  `poids` FLOAT NOT NULL COMMENT 'Poids du prelevement (g)' ,
  `biomasse` FLOAT NULL COMMENT 'Concentration en biomasse (X g/L)' ,
  `nbCellule` FLOAT NULL COMMENT 'Concentration cellulaire (cellules/mL)' ,
  `commentaire` VARCHAR(255) NULL COMMENT 'Commentaire' ,
  PRIMARY KEY (`idPrelevement`) )
AUTO_INCREMENT = 11
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`Poste`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Poste` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Poste` (
  `idPoste` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identifiant du poste' ,
  `agitateur` VARCHAR(50) NOT NULL COMMENT 'Nom du poste d\'agitation' ,
  `vitesse` SMALLINT(5) UNSIGNED NULL COMMENT 'Vitesse d\'agitation (tours/min)' ,
  `ligne` ENUM('A','B','C','D','E') NULL COMMENT 'Ligne de la table d\'agitation (A, B, C, D, E)' ,
  `colonne` ENUM('1','2','3') NULL COMMENT 'Colonne de la table d\'agitation (1, 2, 3)' ,
  `balance` VARCHAR(50) NULL COMMENT 'Modele de la balance' ,
  PRIMARY KEY (`idPoste`) )
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`ConditionCulture`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`ConditionCulture` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`ConditionCulture` (
  `idConditionCulture` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `milieu` VARCHAR(255) NOT NULL COMMENT 'Milieu de culture' ,
  `typeFermenteur` VARCHAR(255) NOT NULL COMMENT 'Type de fermenteur (Erlen, mini-fermenteur, avec ou sans cloche...)' ,
  `volume` FLOAT NULL COMMENT 'Volume de milieu apres innoculation (L)' ,
  `temperature` FLOAT NULL COMMENT 'Temperature en degre Celcius' ,
  `oxygene` VARCHAR(255) NULL COMMENT 'Controle de l\'aeration' ,
  PRIMARY KEY (`idConditionCulture`) )
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`Fermentation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Fermentation` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Fermentation` (
  `idFermentation` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Utilisateur_idUtilisateur` VARCHAR(15) NOT NULL ,
  `Souche_idSouche` SMALLINT(5) UNSIGNED NOT NULL ,
  `ConditionCulture_idConditionCulture` MEDIUMINT(8) UNSIGNED NOT NULL ,
  `Poste_idPoste` MEDIUMINT(8) UNSIGNED NOT NULL ,
  `dateDeclaration` TIMESTAMP NOT NULL COMMENT 'Date de declaration' ,
  `statut` VARCHAR(15) NOT NULL DEFAULT 'stopped' COMMENT 'Statut de la manip: 0, \'en cours\'; 1, \'cloture\'; 2, \'exporte\'' ,
  `commentaire` VARCHAR(255) NULL COMMENT 'Commentaires' ,
  PRIMARY KEY (`idFermentation`, `Souche_idSouche`, `Poste_idPoste`, `Utilisateur_idUtilisateur`, `ConditionCulture_idConditionCulture`) ,
  INDEX `fk_Fermentation_Souche` (`Souche_idSouche` ASC) ,
  INDEX `fk_Fermentation_Poste1` (`Poste_idPoste` ASC) ,
  INDEX `fk_Fermentation_Utilisateur1` (`Utilisateur_idUtilisateur` ASC) ,
  INDEX `fk_Fermentation_ConditionCulture1` (`ConditionCulture_idConditionCulture` ASC) ,
  CONSTRAINT `fk_Fermentation_Souche`
    FOREIGN KEY (`Souche_idSouche` )
    REFERENCES `Fermini`.`Souche` (`idSouche` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Fermentation_Poste1`
    FOREIGN KEY (`Poste_idPoste` )
    REFERENCES `Fermini`.`Poste` (`idPoste` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Fermentation_Utilisateur1`
    FOREIGN KEY (`Utilisateur_idUtilisateur` )
    REFERENCES `Fermini`.`Utilisateur` (`idUtilisateur` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Fermentation_ConditionCulture1`
    FOREIGN KEY (`ConditionCulture_idConditionCulture` )
    REFERENCES `Fermini`.`ConditionCulture` (`idConditionCulture` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `Fermini`.`Acquisition`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Fermini`.`Acquisition` ;

CREATE  TABLE IF NOT EXISTS `Fermini`.`Acquisition` (
  `idAcquisition` INT(10)  NOT NULL AUTO_INCREMENT ,
  `Fermentation_idFermentation` MEDIUMINT(8) UNSIGNED NOT NULL ,
  `Prelevement_idPrelevement` MEDIUMINT(8) UNSIGNED NULL ,
  `dateAcquisition` TIMESTAMP NOT NULL COMMENT 'Date acquisition (timestamp mysql)' ,
  `poids` FLOAT NOT NULL COMMENT 'Poids du fermenteur (g)' ,
  `volumeRestant` FLOAT NOT NULL COMMENT 'Volume de milieu restant (L)' ,
  `temps` FLOAT NOT NULL COMMENT 'Temps de fermentation depuis le debut (h)' ,
  `mCO2` FLOAT NOT NULL COMMENT 'Masse de CO2 degagee ramenee a un litre de mout (g/L)' ,
  `vCO2` FLOAT NULL COMMENT 'Vitesse de degagement du CO2 ramenee a  un litre de mout (g/L/h)' ,
  `temperature` FLOAT NULL COMMENT 'Temperature actuelle du fermenteur (degre celsius)' ,
  PRIMARY KEY (`idAcquisition`, `Fermentation_idFermentation`) ,
  INDEX `fk_Acquisition_Fermentation1` (`Fermentation_idFermentation` ASC) ,
  INDEX `fk_Acquisition_Prelevement1` (`Prelevement_idPrelevement` ASC) ,
  CONSTRAINT `fk_Acquisition_Fermentation1`
    FOREIGN KEY (`Fermentation_idFermentation` )
    REFERENCES `Fermini`.`Fermentation` (`idFermentation` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Acquisition_Prelevement1`
    FOREIGN KEY (`Prelevement_idPrelevement` )
    REFERENCES `Fermini`.`Prelevement` (`idPrelevement` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
PACK_KEYS = 0
ROW_FORMAT = DEFAULT;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


-- -----------------------------------------------------
-- Creation du compte administrateur
-- -----------------------------------------------------
INSERT INTO `Fermini`.`Utilisateur` (
  `idUtilisateur`,
  `nom`,
  `prenom`, 
  `motPasse`,
  `typeUtilisateur`
) 
VALUES (
  'admin', 
  'Administrateur', 
  '', 
  SHA1('admin'), 
  'admin'
);
