SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `lms` ;
CREATE SCHEMA IF NOT EXISTS `lms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `lms` ;

-- -----------------------------------------------------
-- Table `lms`.`storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`storage` ;

CREATE  TABLE IF NOT EXISTS `lms`.`storage` (
  `storageID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `storageRoom` VARCHAR(255) NOT NULL ,
  `storageType` VARCHAR(255) NOT NULL ,
  `storageColumn` CHAR NULL ,
  `storageLine` TINYINT(3) UNSIGNED NULL ,
  PRIMARY KEY (`storageID`) ,
  INDEX `storageIDX` (`storageRoom` ASC, `storageType` ASC) ,
  INDEX `storageCodeIDX` (`storageColumn` ASC, `storageLine` ASC) ,
  UNIQUE INDEX `storageUniqueIDX` (`storageRoom` ASC, `storageType` ASC, `storageColumn` ASC, `storageLine` ASC) )
ENGINE = InnoDB
COMMENT = 'lieux de rangement';


-- -----------------------------------------------------
-- Table `lms`.`saga`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`saga` ;

CREATE  TABLE IF NOT EXISTS `lms`.`saga` (
  `sagaID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `sagaTitle` VARCHAR(255) NOT NULL ,
  `sagaSearchURL` VARCHAR(255) NULL ,
  PRIMARY KEY (`sagaID`) ,
  UNIQUE INDEX `SagaTitleIDX` (`sagaTitle` ASC) )
ENGINE = InnoDB
COMMENT = 'informations sur les sagas';


-- -----------------------------------------------------
-- Table `lms`.`loan`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`loan` ;

CREATE  TABLE IF NOT EXISTS `lms`.`loan` (
  `loanID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `loanHolder` VARCHAR(255) NOT NULL ,
  `loanDate` DATETIME NOT NULL ,
  PRIMARY KEY (`loanID`) ,
  INDEX `LoanHolderIDX` (`loanHolder` ASC) ,
  INDEX `LoanDateIDX` (`loanDate` ASC) )
ENGINE = InnoDB
COMMENT = 'gestion des prêts';


-- -----------------------------------------------------
-- Table `lms`.`book`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`book` ;

CREATE  TABLE IF NOT EXISTS `lms`.`book` (
  `bookID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `bookTitle` VARCHAR(255) NOT NULL ,
  `bookCover` LONGTEXT NULL ,
  `bookSize` VARCHAR(255) NOT NULL ,
  `bookSagaFK` INT UNSIGNED NULL ,
  `bookSagaPosition` TINYINT UNSIGNED NULL ,
  `bookStorageFK` INT UNSIGNED NOT NULL ,
  `bookLoanFK` INT UNSIGNED NULL ,
  `bookDate` DATETIME NOT NULL ,
  PRIMARY KEY (`bookID`) ,
  INDEX `BookTitleIDX` (`bookTitle` ASC) ,
  INDEX `BookSizeIDX` (`bookSize` ASC) ,
  INDEX `BookDateIDX` (`bookDate` ASC) ,
  UNIQUE INDEX `BookUniqueIDX` (`bookTitle` ASC, `bookSize` ASC, `bookSagaFK` ASC) ,
  INDEX `FK_Books_Storages` (`bookStorageFK` ASC) ,
  INDEX `FK_Books_Sagas` (`bookSagaFK` ASC) ,
  INDEX `FK_Books_Loans` (`bookLoanFK` ASC) ,
  INDEX `BookSagaIDX` (`bookSagaFK` ASC, `bookSagaPosition` ASC) ,
  CONSTRAINT `FK_Books_Storages`
    FOREIGN KEY (`bookStorageFK` )
    REFERENCES `lms`.`storage` (`storageID` )
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Books_Sagas`
    FOREIGN KEY (`bookSagaFK` )
    REFERENCES `lms`.`saga` (`sagaID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Books_Loans`
    FOREIGN KEY (`bookLoanFK` )
    REFERENCES `lms`.`loan` (`loanID` )
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'informations sur les livres';


-- -----------------------------------------------------
-- Table `lms`.`author`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`author` ;

CREATE  TABLE IF NOT EXISTS `lms`.`author` (
  `authorID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `authorFirstName` VARCHAR(255) NOT NULL ,
  `authorLastName` VARCHAR(255) NOT NULL ,
  `authorWebSite` VARCHAR(255) NULL ,
  `authorSearchURL` VARCHAR(255) NULL ,
  PRIMARY KEY (`authorID`) ,
  UNIQUE INDEX `AuthorNameIDX` (`authorFirstName` ASC, `authorLastName` ASC) )
ENGINE = InnoDB
COMMENT = 'informations sur les auteurs';


-- -----------------------------------------------------
-- Table `lms`.`books_authors`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`books_authors` ;

CREATE  TABLE IF NOT EXISTS `lms`.`books_authors` (
  `bookFK` INT UNSIGNED NOT NULL ,
  `authorFK` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`bookFK`, `authorFK`) ,
  INDEX `FK_Authors` (`authorFK` ASC) ,
  INDEX `FK_Books` (`bookFK` ASC) ,
  CONSTRAINT `FK_Authors`
    FOREIGN KEY (`authorFK` )
    REFERENCES `lms`.`author` (`authorID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Books`
    FOREIGN KEY (`bookFK` )
    REFERENCES `lms`.`book` (`bookID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'liaison entre livres et auteurs';


-- -----------------------------------------------------
-- Table `lms`.`movie`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`movie` ;

CREATE  TABLE IF NOT EXISTS `lms`.`movie` (
  `movieID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `movieTitle` VARCHAR(255) NOT NULL ,
  `movieGenre` VARCHAR(255) NOT NULL ,
  `movieCover` LONGTEXT NULL ,
  `movieMediaType` VARCHAR(255) NOT NULL ,
  `movieLength` SMALLINT NOT NULL ,
  `movieSagaFK` INT UNSIGNED NULL ,
  `movieSagaPosition` TINYINT UNSIGNED NULL ,
  `movieStorageFK` INT UNSIGNED NOT NULL ,
  `movieLoanFK` INT UNSIGNED NULL ,
  `movieDate` DATETIME NOT NULL ,
  PRIMARY KEY (`movieID`) ,
  INDEX `MovieTitleIDX` (`movieTitle` ASC) ,
  INDEX `MovieDateIDX` (`movieDate` ASC) ,
  INDEX `MovieMediaTypeIDX` (`movieMediaType` ASC) ,
  INDEX `MovieGenreIDX` (`movieGenre` ASC) ,
  INDEX `FK_Movies_Storages` (`movieStorageFK` ASC) ,
  INDEX `FK_Movies_Sagas` (`movieSagaFK` ASC) ,
  INDEX `FK_Movies_Loans` (`movieLoanFK` ASC) ,
  INDEX `MovieSagaIDX` (`movieSagaFK` ASC, `movieSagaPosition` ASC) ,
  UNIQUE INDEX `MovieUniqueIDX` (`movieTitle` ASC, `movieMediaType` ASC, `movieSagaFK` ASC) ,
  CONSTRAINT `FK_Movies_Storages`
    FOREIGN KEY (`movieStorageFK` )
    REFERENCES `lms`.`storage` (`storageID` )
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Movies_Sagas`
    FOREIGN KEY (`movieSagaFK` )
    REFERENCES `lms`.`saga` (`sagaID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Movies_Loans`
    FOREIGN KEY (`movieLoanFK` )
    REFERENCES `lms`.`loan` (`loanID` )
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'informations sur les films';


-- -----------------------------------------------------
-- Table `lms`.`artist`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`artist` ;

CREATE  TABLE IF NOT EXISTS `lms`.`artist` (
  `artistID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `artistFirstName` VARCHAR(255) NOT NULL ,
  `artistLastName` VARCHAR(255) NOT NULL ,
  `artistPhoto` LONGTEXT NULL ,
  PRIMARY KEY (`artistID`) ,
  UNIQUE INDEX `ArtistNameIDX` (`artistFirstName` ASC, `artistLastName` ASC) )
ENGINE = InnoDB
COMMENT = 'informations sur les artistes';


-- -----------------------------------------------------
-- Table `lms`.`movies_artists`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`movies_artists` ;

CREATE  TABLE IF NOT EXISTS `lms`.`movies_artists` (
  `movieFK` INT UNSIGNED NOT NULL ,
  `artistFK` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`movieFK`, `artistFK`) ,
  INDEX `FK_Artists` (`artistFK` ASC) ,
  INDEX `FK_Movies` (`movieFK` ASC) ,
  CONSTRAINT `FK_Artists`
    FOREIGN KEY (`artistFK` )
    REFERENCES `lms`.`artist` (`artistID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Movies`
    FOREIGN KEY (`movieFK` )
    REFERENCES `lms`.`movie` (`movieID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'liaison entre films et artistes';


-- -----------------------------------------------------
-- Table `lms`.`album`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`album` ;

CREATE  TABLE IF NOT EXISTS `lms`.`album` (
  `albumID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `albumTitle` VARCHAR(255) NOT NULL ,
  `albumType` VARCHAR(255) NOT NULL ,
  `albumCover` LONGTEXT NULL ,
  `albumStorageFK` INT UNSIGNED NOT NULL ,
  `albumLoanFK` INT UNSIGNED NULL ,
  `albumDate` DATETIME NOT NULL ,
  PRIMARY KEY (`albumID`) ,
  INDEX `AlbumTitleIDX` (`albumTitle` ASC) ,
  INDEX `AlbumDateIDX` (`albumDate` ASC) ,
  UNIQUE INDEX `AlbumUniqueIDX` (`albumTitle` ASC, `albumType` ASC) ,
  INDEX `FK_Albums_Storages` (`albumStorageFK` ASC) ,
  INDEX `FK_Albums_Loans` (`albumLoanFK` ASC) ,
  CONSTRAINT `FK_Albums_Storages`
    FOREIGN KEY (`albumStorageFK` )
    REFERENCES `lms`.`storage` (`storageID` )
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Albums_Loans`
    FOREIGN KEY (`albumLoanFK` )
    REFERENCES `lms`.`loan` (`loanID` )
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'informations sur les albums';


-- -----------------------------------------------------
-- Table `lms`.`band`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`band` ;

CREATE  TABLE IF NOT EXISTS `lms`.`band` (
  `bandID` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `bandName` VARCHAR(255) NOT NULL ,
  `bandGenre` VARCHAR(255) NOT NULL ,
  `bandWebSite` VARCHAR(255) NOT NULL ,
  `bandLastCheckDate` DATETIME NULL ,
  PRIMARY KEY (`bandID`) ,
  UNIQUE INDEX `BandNameIDX` (`bandName` ASC, `bandGenre` ASC) ,
  INDEX `BandLastCheckDateIDX` (`bandLastCheckDate` ASC) )
ENGINE = InnoDB
COMMENT = 'informations sur les groupes';


-- -----------------------------------------------------
-- Table `lms`.`albums_bands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`albums_bands` ;

CREATE  TABLE IF NOT EXISTS `lms`.`albums_bands` (
  `albumFK` INT UNSIGNED NOT NULL ,
  `bandFK` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`albumFK`, `bandFK`) ,
  INDEX `FK_Bands` (`bandFK` ASC) ,
  INDEX `FK_Albums` (`albumFK` ASC) ,
  CONSTRAINT `FK_Bands`
    FOREIGN KEY (`bandFK` )
    REFERENCES `lms`.`band` (`bandID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_Albums`
    FOREIGN KEY (`albumFK` )
    REFERENCES `lms`.`album` (`albumID` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'liaison entre les albums et les groupes';


-- -----------------------------------------------------
-- Table `lms`.`list_timestamp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lms`.`list_timestamp` ;

CREATE  TABLE IF NOT EXISTS `lms`.`list_timestamp` (
  `list_name` VARCHAR(255) NOT NULL ,
  `list_timestamp` TIMESTAMP NOT NULL ,
  PRIMARY KEY (`list_name`) )
ENGINE = InnoDB
COMMENT = 'utilisée pour gérer le cache des listes';


-- -----------------------------------------------------
-- Placeholder table for view `lms`.`books_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`books_view` (`bookID` INT, `bookTitle` INT, `bookSize` INT, `bookCover` INT, `bookDate` INT, `sagaID` INT, `sagaTitle` INT, `bookSagaPosition` INT, `bookSagaSize` INT, `sagaSearchURL` INT, `storageID` INT, `storageRoom` INT, `storageType` INT, `storageColumn` INT, `storageLine` INT, `loanID` INT, `loanHolder` INT, `loanDate` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`movies_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`movies_view` (`movieID` INT, `movieTitle` INT, `movieCover` INT, `movieMediaType` INT, `movieLength` INT, `movieGenre` INT, `movieDate` INT, `sagaID` INT, `sagaTitle` INT, `movieSagaPosition` INT, `movieSagaSize` INT, `sagaSearchURL` INT, `storageID` INT, `storageRoom` INT, `storageType` INT, `storageColumn` INT, `storageLine` INT, `loanID` INT, `loanHolder` INT, `loanDate` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`book_authors_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`book_authors_view` (`bookFK` INT, `authorID` INT, `authorFirstName` INT, `authorLastName` INT, `authorWebSite` INT, `authorSearchURL` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`movie_artists_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`movie_artists_view` (`movieFK` INT, `artistID` INT, `artistFirstName` INT, `artistLastName` INT, `artistPhoto` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`book_saga_count_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`book_saga_count_view` (`bookSagaFK` INT, `bookSagaSize` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`movie_saga_count_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`movie_saga_count_view` (`movieSagaFK` INT, `movieSagaSize` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`album_bands_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`album_bands_view` (`albumFK` INT, `bandID` INT, `bandName` INT, `bandGenre` INT, `bandWebSite` INT, `bandSearchURL` INT, `bandLastCheckDate` INT);

-- -----------------------------------------------------
-- Placeholder table for view `lms`.`albums_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lms`.`albums_view` (`albumID` INT, `albumTitle` INT, `albumType` INT, `albumCover` INT, `albumDate` INT, `storageID` INT, `storageRoom` INT, `storageType` INT, `storageColumn` INT, `storageLine` INT, `loanID` INT, `loanHolder` INT, `loanDate` INT);

-- -----------------------------------------------------
-- View `lms`.`books_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`books_view` ;
DROP TABLE IF EXISTS `lms`.`books_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`books_view` AS
SELECT 
    bookID, bookTitle, bookSize, bookCover, bookDate,
    sagaID, sagaTitle, bookSagaPosition, bookSagaSize, sagaSearchURL,
    storageID, storageRoom, storageType, storageColumn, storageLine,
    loanID, loanHolder, loanDate
FROM lms.book B
INNER JOIN lms.storage ST ON bookStorageFK = storageID
LEFT JOIN lms.loan L ON bookLoanFK = loanID
LEFT JOIN lms.saga S ON B.bookSagaFK = sagaID
LEFT JOIN lms.book_saga_count_view BSCV ON BSCV.bookSagaFK = B.bookSagaFK;

-- -----------------------------------------------------
-- View `lms`.`movies_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`movies_view` ;
DROP TABLE IF EXISTS `lms`.`movies_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`movies_view` AS
SELECT 
    movieID, movieTitle, movieCover, movieMediaType, movieLength, movieGenre, movieDate, 
    sagaID, sagaTitle, movieSagaPosition, movieSagaSize, sagaSearchURL,
    storageID, storageRoom, storageType, storageColumn, storageLine,
    loanID, loanHolder, loanDate
FROM lms.movie M
INNER JOIN lms.storage ST ON movieStorageFK = storageID
LEFT JOIN lms.loan L ON movieLoanFK = loanID
LEFT JOIN lms.saga S ON M.movieSagaFK = sagaID
LEFT JOIN lms.movie_saga_count_view MSCV ON MSCV.movieSagaFK = M.movieSagaFK;

-- -----------------------------------------------------
-- View `lms`.`book_authors_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`book_authors_view` ;
DROP TABLE IF EXISTS `lms`.`book_authors_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`book_authors_view` AS
SELECT 
    DISTINCT(bookFK),
    authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL
FROM lms.books_authors BA
INNER JOIN lms.author A ON authorFK = authorID

;

-- -----------------------------------------------------
-- View `lms`.`movie_artists_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`movie_artists_view` ;
DROP TABLE IF EXISTS `lms`.`movie_artists_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`movie_artists_view` AS
SELECT 
    DISTINCT(movieFK),
    artistID, artistFirstName, artistLastName, artistPhoto
FROM lms.movies_artists FA
INNER JOIN lms.artist A ON artistFK = artistID

;

-- -----------------------------------------------------
-- View `lms`.`book_saga_count_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`book_saga_count_view` ;
DROP TABLE IF EXISTS `lms`.`book_saga_count_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`book_saga_count_view` AS
SELECT DISTINCT(bookSagaFK), COUNT(bookSagaFK) AS bookSagaSize
FROM lms.book
GROUP BY bookSagaFK;

-- -----------------------------------------------------
-- View `lms`.`movie_saga_count_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`movie_saga_count_view` ;
DROP TABLE IF EXISTS `lms`.`movie_saga_count_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`movie_saga_count_view` AS
SELECT DISTINCT(movieSagaFK), COUNT(movieSagaFK) AS movieSagaSize
FROM lms.movie
GROUP BY movieSagaFK;

-- -----------------------------------------------------
-- View `lms`.`album_bands_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`album_bands_view` ;
DROP TABLE IF EXISTS `lms`.`album_bands_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`album_bands_view` AS
SELECT 
    DISTINCT(albumFK),
    bandID, bandName, bandGenre, bandWebSite, bandSearchURL, bandLastCheckDate
FROM lms.albums_bands AB
INNER JOIN lms.band B ON bandFK = bandID

;

-- -----------------------------------------------------
-- View `lms`.`albums_view`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `lms`.`albums_view` ;
DROP TABLE IF EXISTS `lms`.`albums_view`;
USE `lms`;
CREATE  OR REPLACE VIEW `lms`.`albums_view` AS
SELECT 
    albumID, albumTitle, albumType, albumCover, albumDate,
    storageID, storageRoom, storageType, storageColumn, storageLine,
    loanID, loanHolder, loanDate
FROM lms.album A
INNER JOIN lms.storage ST ON albumStorageFK = storageID
LEFT JOIN lms.loan L ON albumLoanFK = loanID
;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
