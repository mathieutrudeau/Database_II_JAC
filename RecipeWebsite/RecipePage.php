<?php
$username="user1754757";
$password="password";
$dbname="//10.39.167.152/pdborcl";

$conn=oci_connect($username, $password, $dbname);

$pageTitle="Keen Cooks";

if($conn)
{
    // Id of the recipe to show.
    $recID=$_GET['id'];

    // Page HTML.
    $recipePageHtml="";

    // Add head of page + CSS.
    //===============================================
    $recipePageHtml.="<!DOCTYPE html>
    <html lang='en'>
    <head>
      <title>".$pageTitle."</title>
      <meta charset='utf-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1'>
      <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>
      <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js'></script>
      <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js'></script>
      <script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'></script>
      <style>
      .fakeimg {
        height: 200px;
        background: rgb(123, 202, 255);
      }

      table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }
      
      td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }
      
      tr:nth-child(even) {
        background-color: #dddddd;
      }
      ul#tools li {
        display:inline;
      }
      </style>
    </head>
    <body>";
    
    // Retrieve EVERYTHING about the recipe itself.
    //==============================================
    $stmt=oci_parse($conn, "SELECT RecName, RecDesc, PrepTime, CookTime,(CookTime+PrepTime) AS TotalTime, Serves, Instructions, UsefulTip, AverageRating, TotalReviews FROM Recipes WHERE recID=:recid");
    oci_bind_by_name($stmt, ':recid', $recID, -1);
    oci_execute($stmt, OCI_DEFAULT);
    $row=oci_fetch_array($stmt, OCI_ASSOC);
    //==============================================


    // Get values from query.
    //==============================================
    $rating=$row['AVERAGERATING'];
    $reviewsNum=$row['TOTALREVIEWS'];
    $description=$row['RECDESC'];
    $prepTime=$row['PREPTIME'];
    $cookTime=$row['COOKTIME'];
    $totalTime=$row['TOTALTIME'];
    $serves="N/A";
    if(isset($row['SERVES'])){
        $serves=$row['SERVES'];
    }
    $instructions=$row['INSTRUCTIONS'];
    $usefulTip="N/A";
    if(isset($row['USEFULTIP'])){
        $usefulTip=$row['USEFULTIP'];
    }
    //==============================================

    // Show recipe name
    //==============================================
    $recipePageHtml.="  <!-- TITLE SECTION -->
    <div class='jumbotron text-center' style='margin-bottom:0'>";

    $recipePageHtml.="<h1>".$row['RECNAME']."</h1>";
    $recipePageHtml.="<p><b>";
    //==============================================

    // Add the rating stars & Description
    //==============================================
    for($i=1;$i<($rating-($rating%1));$i++){
        $recipePageHtml.="*";
    }
    $recipePageHtml.="</b>";
    $recipePageHtml.=" ($rating-star rating) ($reviewsNum reviews)";
    $recipePageHtml.="</p>"; 
    $recipePageHtml.="<h6>$description</h6>";
    //==============================================

    // NAVIGATION BAR : CATEGORIES
    //==============================================
    $recipePageHtml.="</div>";
    $recipePageHtml.="  <!-- NAVIGATION BAR : CATEGORIES -->
    <nav class='navbar navbar-expand-sm bg-light navbar-light'>
      <a class='navbar-brand' href='MainPage.php'>Main Page</a>
      <a class='navbar-brand' href='#'>Categories</a>
      <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapsibleNavbar'>
        <span class='navbar-toggler-icon'></span>
      </button>
      <div class='collapse navbar-collapse' id='collapsibleNavbar'>";
    
    $stid=oci_parse($conn, 'SELECT catID, catName, numberOfRecipes FROM VWCategoriesAndRecipes');
    oci_execute($stid);

    $recipePageHtml.="<ul class='navbar-nav'>";
    while(($row=oci_fetch_array($stid, OCI_ASSOC)))
    {
        $recipePageHtml.="<li 'nav-item'><a class='nav-link' href='CategoryPage.php?id=".$row['CATID']."'>".$row['CATNAME']." (".$row['NUMBEROFRECIPES'].")</a></li>";
    }
    $recipePageHtml.="</ul>";
    //==============================================
      
    // NAVIGATION BAR : INGREDIENT SEARCH
    //==============================================
    $recipePageHtml.="</div>
      <form method='POST' action='SearchResultPage.php'>
      <input type='text' name='searchIngredient' value='Search Ingredients..'>
      </form>
    </nav>";
    //==============================================

    $recipePageHtml.="<div class='container' style='margin-top:30px'>
        <div class='row'>
          <div class='col-sm-4'>
          <div><img class='fakeimg' src='RecipeImagesUpdated/".$recID.".png'/></div>";
    
    $recipePageHtml.="<hr class='d-sm-none'></div>";

    // Retrieve the ingredients.
    //==============================================
    $stmt2=oci_parse($conn, "SELECT ingid, ingName, quantity, Measure FROM Ingredients NATURAL JOIN IngredientsInRecipes WHERE recID=:recid");
    oci_bind_by_name($stmt2, ':recid', $recID, -1);
    oci_execute($stmt2, OCI_DEFAULT);
    //==============================================

    // Show all ingredients.
    //==============================================
    $recipePageHtml.="<div class='col-sm-4'><h5>Ingredients:</h5><ul>";
    while(($row=oci_fetch_array($stmt2, OCI_ASSOC))){
        $recipePageHtml.="<li>";
        if(isset($row['QUANTITY'])){
            $quantity=$row['QUANTITY'];
            switch($quantity){
                case ".25":
                    $quantity="1/4";
                    break;
                case ".5":
                    $quantity="1/2";
                    break;
                case ".75":
                    $quantity="3/4";
                    break;
                default:
                    break;
            }
            $recipePageHtml.=$quantity;
        }
        if(isset($row['MEASURE'])){
            $recipePageHtml.=" ".$row['MEASURE'];
        }
        if(isset($row['INGNAME'])){
            $recipePageHtml.=" ".$row['INGNAME'];
			$stmtAltIng=oci_parse($conn, "SELECT ingName FROM Ingredients WHERE ingid=(SELECT Altingid FROM AlternativeIngredients WHERE ingid=:ingid)");
			oci_bind_by_name($stmtAltIng, ':ingid', $row['INGID'], -1);
			oci_execute($stmtAltIng, OCI_DEFAULT);
			$row1=oci_fetch_array($stmtAltIng, OCI_ASSOC);
			if(isset($row1['INGNAME'])){
				$recipePageHtml.=" ( or ".$row1['INGNAME'].")";
			}
			oci_free_statement($stmtAltIng);
        }
        $recipePageHtml.="</li>";
    }
    $recipePageHtml.="</ul></div>";
    //==============================================================================

    // Show the categories, the preparation time, the cook time and the number of servings
    //==============================================
    $stmt3=oci_parse($conn, "SELECT catName FROM RecipesInCategories NATURAL JOIN Categories WHERE recID=:recid");
    oci_bind_by_name($stmt3, ':recid', $recID, -1);
    oci_execute($stmt3, OCI_DEFAULT);

    $recipePageHtml.="<div class='col-sm-4'>";
    while(($row=oci_fetch_array($stmt3, OCI_ASSOC))){
        $recipePageHtml.="<br/>";
        $recipePageHtml.="<h6> -> ".$row['CATNAME']."</h6>";
    }          
    $recipePageHtml.="</div>";

    $recipePageHtml.="</div></div><div class='container' style='margin-top:30px'><div class='row'>";

    $recipePageHtml.="<div class='col-sm-12'>";
    $recipePageHtml.="<p><b>Prep Time: </b>".$prepTime." minute(s)<hr/><b>Cook Time: </b>".$cookTime." minute(s)<hr/><b>Serves: </b>".$serves."</p><hr/>";
    //==============================================

    // Show Instructions
    //==============================================
    $recipePageHtml.="<h5>Instructions:</h5><ol>";
    $instructionsArr=explode('.',$instructions);
    for($i=1; $i<count($instructionsArr);$i++){
        $recipePageHtml.="<li>".$instructionsArr[($i-1)].".</li>";
    }
    $recipePageHtml.="</ol><hr/><br/>";
    //==============================================

    // Show Nutritional Information
    //==============================================
    $stmt4=oci_parse($conn, "SELECT kCal, fat, carbs, sugars, fibre, protein, salt, appliesto FROM NUTRITIONALINFO WHERE recID=:recid");
    oci_bind_by_name($stmt4, ':recid', $recID, -1);
    oci_execute($stmt4, OCI_DEFAULT);
    $row=oci_fetch_array($stmt4, OCI_ASSOC);

    if(isset($row['KCAL'])){
    $recipePageHtml.="<h5>Nutritional Information: (".$row['APPLIESTO'].")</h5>
    <table><tr><td>kcal</td><td>fat</td><td>carbs</td><td>sugars</td><td>fibre</td><td>protein</td><td>salt</td></tr><tr><td>".$row['KCAL']."</td><td>".$row['FAT']."g</td><td>".$row['CARBS']."g</td><td>".$row['SUGARS']."g</td><td>".$row['FIBRE']."g</td><td>".$row['PROTEIN']."g</td><td>".$row['SALT']."g</td></tr></table>";
    }
    else{
        $recipePageHtml.="<h5>Nutritional Information: N/A</h5>";
    }
    //==============================================

    // Show good pairing recipes
    //==============================================
    $stmt5=oci_parse($conn, "SELECT recName FROM recipes WHERE recID in (SELECT pairrecid FROM PairsWith WHERE recID=:recid)");
    oci_bind_by_name($stmt5, ':recid', $recID, -1);
    oci_execute($stmt5, OCI_DEFAULT);

    $recipePageHtml.="<hr/><h5>Goes Well With:</h5><ul>";
    while(($row=oci_fetch_array($stmt5, OCI_ASSOC))){
        $recipePageHtml.="<li>".$row['RECNAME']."</li>";
    }
    $recipePageHtml.="</ul>";
    //==============================================

    // Show Useful Tips
    //==============================================
    $recipePageHtml.="<hr/><h5>Recipe Tip:</h5><p>".$usefulTip."</p>";
    //==============================================
    
    // Tools needed for the recipe
    //==============================================
    $stmt6=oci_parse($conn, "SELECT ToolName FROM ToolsInRecipe NATURAL JOIN Tools WHERE recID=:recid");
    oci_bind_by_name($stmt6, ':recid', $recID, -1);
    oci_execute($stmt6, OCI_DEFAULT);

    $recipePageHtml.="<hr/><h5>Tools Needed:</h5>";

    $recipePageHtml.="<ul id='tools'>";
    while(($row=oci_fetch_array($stmt6, OCI_ASSOC))){
          $recipePageHtml.="<li> • ".$row['TOOLNAME']."</li>";
    }
    $recipePageHtml.="</ul><hr/><br/>";
    //==============================================

    // Review Section
    //==============================================
    // Add the review
    if(isset($_POST['rating'])){
      $rating=$_POST['rating'];
      $comment="";
        if(isset($_POST['comment'])){      
            $comment=$_POST['comment'];
        }
    if(isset($_POST['share'])){
    $stmt7=oci_parse($conn, "INSERT INTO Reviews (recID, Rating, Comments) VALUES (:recid,:rating,:comments)");
    oci_bind_by_name($stmt7, ':recid', $recID, -1);
    oci_bind_by_name($stmt7, ':rating', $rating, -1);
    oci_bind_by_name($stmt7, ':comments', $comment, -1);
    oci_execute($stmt7, OCI_DEFAULT);
    $stmt8=oci_parse($conn, "commit");
    oci_execute($stmt8, OCI_DEFAULT);
      }
    }

    // Form to enter a review
    $recipePageHtml.="<h4>Rate this Recipe!</h4><br/>";
    $recipePageHtml.="<form action='#' method='post'>";
    $recipePageHtml.="
    <label><h5>Rating:</h5></label>
    <ul>
    <li>0 <input type='radio' name='rating' group='Ratings' value='0'></li>
    <li>1 <input type='radio' name='rating' group='Ratings' value='1'></li>
    <li>2 <input type='radio' name='rating' group='Ratings' value='2'></li>
    <li>3 <input type='radio' name='rating' group='Ratings' value='3'></li>
    <li>4 <input type='radio' name='rating' group='Ratings' value='4'></li>
    <li>5 <input type='radio' name='rating' group='Ratings' value='5'></li>
    </ul>
    <br/>
    <label><h5>Share your Thoughts! (200 words)</h5></label>
    <br/>
    <textarea name='comment' style='height:200px;width:600px'></textarea><br />
    <input type='submit' name='share' value='Share'>";
    $recipePageHtml.="</form><hr/><br/>";
    //==============================================

    // See other reviews
    //==============================================
    $recipePageHtml.="<h4>Reviews</h4>";
    $recipePageHtml.="<br/><hr/>";
    $stmt8=oci_parse($conn, "SELECT Rating, Comments From reviews WHERE recID=:recid");
    oci_bind_by_name($stmt8, ':recid', $recID, -1);
    oci_execute($stmt8, OCI_DEFAULT);
    while(($row=oci_fetch_array($stmt8, OCI_ASSOC))){
      $recipePageHtml.="<h5>".$row['RATING'];
      if(isset($row['COMMENTS'])){
        $recipePageHtml.=" - ".$row['COMMENTS'];
      }
      $recipePageHtml.="</h5>";
    }
    //==============================================

    // End Of Page
    //==============================================
    $recipePageHtml.="<br/></div>";
    $recipePageHtml.="</div></div>";
    $recipePageHtml.="<div class='jumbotron text-center' style='margin-bottom:0'>
        <p>Copyrighted by Mathieu Trudeau, Keen Cooks Inc. 2019</p>
      </div></body></html>";
    //==============================================

    echo $recipePageHtml;
}
else
{
    echo "<p>Something went wrong...</p>";
}

oci_close($conn);
?>