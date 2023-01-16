-- Assignment #3

-- Views
Create View vwOneHourOrLess as
Select RecID, RecName, (PrepTime+CookTime) as TotalTime 
From Recipes 
Where (PrepTime+CookTime) <= 60
Order By (PrepTime+CookTime) ASC;

Select * From vwOneHourOrLess;

----------------------------------
Create View vwFiveIngredientsOrLess as
Select RecID, RecName, Count(*) as NumberOfIngredients
From IngredientsInRecipes
Natural Join Recipes
Group By RecID, RecName
Having Count(*) <= 5
Order By NumberOfIngredients ASC;

Select recID, RecName, NumberOfIngredients from vwfiveingredientsorless;

----------------------------------
Create View vwCategoriesAndRecipes as
Select CatID, CatName, Count(*) as NumberOfRecipes
From Categories
Natural Join RecipesInCategories
Group By CatID, CatName
Order By CatName ASC;

Select * From vwcategoriesandrecipes;


-- Functions
/
Create Or Replace Function ChooseRandomRecipe
RETURN INTEGER
AS
minID INTEGER;
maxID INTEGER;
randomID INTEGER;
BEGIN

Select MAX(RecID) INTO maxID From Recipes;
Select MIN(RecID) INTO minID From Recipes;

randomID := DBMS_RANDOM.VALUE(minID, maxID);

RETURN randomID;
END;
/

Select ChooseRandomRecipe From Dual;
SELECT RecID, RecName, RecDesc, (prepTime+CookTime) as TotalTime, NVL(Serves, 0) as Serves FROM Recipes WHERE recID=(SELECT ChooseRandomRecipe FROM Dual);

-------------------------------------------------
/
Create Or Replace Function ConvertOvenTemp
(
    amount NUMBER,
    inType CHAR,
    outType CHAR
)
RETURN NUMBER
AS
convertedAmount NUMBER;
BEGIN
 CASE inType
    WHEN 'F' THEN 
         CASE outType
            WHEN 'F' THEN convertedAmount := amount;
            WHEN 'C' THEN convertedAmount := (amount-32)*5/9;
            WHEN 'G' THEN 
                IF(amount<225) THEN
                    convertedAmount := 0;
                ELSIF(amount<250) THEN
                    convertedAmount := 0.25;
                ELSIF(amount<275) THEN
                    convertedAmount := 0.5;
                ELSE
                    convertedAmount := ROUND((((amount-32)*5/9)-121)/14,0);
                    IF(convertedAmount>10) THEN
                        convertedAmount := 10;
                    END IF;
                END IF;
        END CASE;
    WHEN 'C' THEN 
         CASE outType
            WHEN 'F' THEN convertedAmount := (amount * 9/5)+32;
            WHEN 'C' THEN convertedAmount := amount;
            WHEN 'G' THEN
                IF(amount<107) THEN
                    convertedAmount := 0;
                ELSIF(amount<121) THEN
                    convertedAmount := 0.25;
                ELSIF(amount<135) THEN
                    convertedAmount := 0.5;
                ELSE
                    convertedAmount := ROUND((amount-121)/14,0);
                    IF(convertedAmount>10) THEN
                        convertedAmount := 10;
                    END IF;
                END IF;
        END CASE;
    WHEN 'G' THEN 
        CASE outType
            WHEN 'F' THEN 
                IF(amount<0.25) THEN
                    convertedAmount := 0;
                ELSIF(amount<0.5) THEN
                    convertedAmount := 225;
                ELSIF(amount<1) THEN
                    convertedAmount := 250;
                ELSE
                    convertedAmount := (((amount*14)+121)*9/5)+35;
                END IF;
            WHEN 'C' THEN 
                IF(amount<0.25) THEN
                    convertedAmount := 0;
                ELSIF(amount<0.5) THEN
                    convertedAmount := 107;
                ELSIF(amount<1) THEN
                    convertedAmount := 121;
                ELSE
                    convertedAmount := (amount*14)+121;
                END IF;
            WHEN 'G' THEN convertedAmount := amount;
        END CASE;
 END CASE;
 
RETURN convertedAmount;
END;
/
SELECT ConvertOvenTemp(0,'C','C') as Temp From Dual;
SELECT * FROM CATEGORIEs;
-- Triggers
/
Create Or Replace Trigger  Ingredients_b_i_u
Before Insert On Ingredients For Each Row
Declare 
    ingname VARCHAR(50);
BEGIN
    ingname := :new.IngName;
    ingname := INITCAP(ingname);
    :new.IngName := ingname;
END;
/

Insert Into Ingredients (IngName, IngTypeID) VALUES('tilapia (fresh)','M');
Insert Into Ingredients (IngName, IngTypeID) VALUES('cod (fresh)','M');
Select * From Ingredients order by ingname;
Select * From ingredienttypes;

----------------------------------------------------
/
Create Table Reviews
(
    ReviewID NUMBER(10,0) Generated Always As Identity,
    RecID NUMBER(10,0),
    Rating NUMBER(1,0),
    Comments VARCHAR2(200),
    CONSTRAINT Reviews_ReviewID_pk Primary Key (ReviewID),
    CONSTRAINT Reviews_RecID_fk Foreign Key (RecID) References Recipes(RecID),
    CONSTRAINT Reviews_Rating_ck Check ((Rating) Between 0 And 5)
);

Drop Table Reviews;
/
/
Create Or Replace Trigger Reviews_a_i
Before Insert On Reviews For Each Row
Declare 
avgRating NUMBER(3,2);
totalRev NUMBER(5,0);
rID NUMBER(10,0);
rating NUMBER(1,0);
newavg NUMBER(3,2);
sumrating NUMBER(10,0);
BEGIN
    rID := :new.RecID;
    rating := :new.Rating;
    Select TotalReviews Into totalRev From Recipes Where RecID=rID;
    Select AverageRating Into avgRating From Recipes Where RecID=rID;
    sumrating := avgRating*totalRev;
    totalRev := totalRev+1;
    sumrating := sumrating+rating;
    newavg := sumrating/totalRev;
    Update Recipes Set AverageRating=newavg,TotalReviews=totalRev Where RecID=rID;
END;
/


Insert Into Reviews (RecID,Rating,Comments) Values (1,3,'Not bad');
Insert Into Reviews (RecID,Rating,Comments) Values (1,5,'Excellent!');
Insert Into Reviews (RecID,Rating,Comments) Values (2,4,'Good!');
Insert Into Reviews (RecID,Rating,Comments) Values (3,1,'Awful!');
Insert Into Reviews (RecID,Rating,Comments) Values (1,4,'Decent');

Select * From Reviews;
Select recID, totalReviews,averageRating From Recipes;



-----------------------------------------
Select recID, RecName, AverageRating, TotalReviews From Recipes Order By AverageRating DESC Fetch First 3 Rows Only;


SELECT ingid, ingName, quantity, Measure FROM Ingredients NATURAL JOIN IngredientsInRecipes WHERE recID=1;


SELECT recName FROM recipes WHERE recID in (SELECT pairrecid FROM PairsWith WHERE recID=1);

















