DROP TABLE IF EXISTS books_view_ft;
DROP TABLE IF EXISTS book_authors_view_ft;

CREATE TABLE books_view_ft AS
SELECT  bookID, bookTitle, bookSize, bookDate,
		sagaID, sagaTitle, bookSagaPosition, bookSagaSize, sagaSearchURL,
		storageID, storageRoom, storageType, storageColumn, storageLine,
		loanID, loanHolder, loanDate
FROM books_view;

ALTER TABLE books_view_ft ENGINE = MyISAM,
ADD FULLTEXT INDEX bookFT (bookTitle),
ADD FULLTEXT INDEX sagaFT (sagaTitle),
ADD FULLTEXT INDEX loanFT (loanHolder);


CREATE TABLE book_authors_view_ft AS
SELECT bookFK, authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL, CONCAT(authorFirstName, ' ', authorLastName) AS authorFullName
FROM book_authors_view;

ALTER TABLE book_authors_view_ft ENGINE = MyISAM,
ADD FULLTEXT INDEX authorFullNameFT (authorFullName),
ADD FULLTEXT INDEX authorFirstNameFT (authorFirstName),
ADD FULLTEXT INDEX authorLastNameFT (authorLastName),
ADD INDEX bookFK (bookFK);

SELECT * 
FROM books_view_ft 
INNER JOIN book_authors_view_ft ON bookID = bookFK 
WHERE MATCH(authorFirstName) AGAINST ('Dan') 