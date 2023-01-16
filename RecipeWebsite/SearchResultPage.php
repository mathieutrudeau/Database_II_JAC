<?php
$username="user1754757";
$password="password";
$dbname="//10.39.167.152/pdborcl";

$conn=oci_connect($username, $password, $dbname);

$pageTitle="Keen Cooks";



if($conn)
{
    // Ingredient to find
    $ingredient=$_POST['searchIngredient'];

    // Page HTML.
    $searchPageHtml="";

    // Add head of page + CSS.
    //==============================================
    $searchPageHtml.="<!DOCTYPE html>
    <html lang='en'>
    <head>
      <title>$pageTitle</title>
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
      </style>
    </head>
    <body>";
    //==============================================

    // Show the searched for ingredient
    //==============================================
    $searchPageHtml.="<!-- TITLE SECTION -->
    <div class='jumbotron text-center' style='margin-bottom:0'>
      <h1>You Searched for $ingredient</h1>
      <p>Recipes that contain '$ingredient'</p> 
    </div>";
    //==============================================
    
    // NAVIGATION BAR : CATEGORIES
    //==============================================
    $searchPageHtml.="</div>";
    $searchPageHtml.="  <!-- NAVIGATION BAR : CATEGORIES -->
    <nav class='navbar navbar-expand-sm bg-light navbar-light'>
      <a class='navbar-brand' href='MainPage.php'>Main Page</a>
      <a class='navbar-brand' href='#'>Categories</a>
      <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapsibleNavbar'>
        <span class='navbar-toggler-icon'></span>
      </button>
      <div class='collapse navbar-collapse' id='collapsibleNavbar'>";
    
    $stid=oci_parse($conn, 'SELECT catID, catName, numberOfRecipes FROM VWCategoriesAndRecipes');
    oci_execute($stid);

    $searchPageHtml.="<ul class='navbar-nav'>";
    while(($row=oci_fetch_array($stid, OCI_ASSOC)))
    {
        $searchPageHtml.="<li 'nav-item'><a class='nav-link' href='CategoryPage.php?id=".$row['CATID']."'>".$row['CATNAME']." (".$row['NUMBEROFRECIPES'].")</a></li>";
    }
    $searchPageHtml.="</ul>";
    //==============================================
      
    // NAVIGATION BAR : INGREDIENT SEARCH
    //==============================================
    $searchPageHtml.="</div>
      <form method='POST' action='SearchResultPage.php'>
      <input type='text' name='searchIngredient' value='Search Ingredients..'>
      </form>
    </nav>";
    //==============================================

    // Show all the recipes that contain that ingredient
    //==============================================
    $searchPageHtml.="<div class='container' style='margin-top:30px'>
    <div class='row'>
    <div class='col-sm-12'>";

    $ingredient=strtoupper($ingredient);
    $ingredient="%"."$ingredient"."%";

    $stmt1=oci_parse($conn, "SELECT recID, recName, recDesc,(prepTime+CookTime) AS TotalTime, Serves, averageRating, totalReviews FROM Recipes WHERE RECID IN 
    (SELECT recID FROM IngredientsInRecipes WHERE INGID IN
    (SELECT INGID FROM Ingredients WHERE SOUNDEX(INGNAME)=SOUNDEX(:ingr) OR UPPER(INGNAME) like :ingr))");
    oci_bind_by_name($stmt1, ':ingr', $ingredient, -1);
    oci_execute($stmt1);

    $noRecipe=true;

    // Show recipes
    while(($row=oci_fetch_array($stmt1, OCI_ASSOC))){
      $noRecipe=false;
      $servings="N/A";
            if (count($row)==7){
                $servings=$row['SERVES'];
            }
            $recID=$row['RECID'];
            $searchPageHtml.="<h2>".$row['RECNAME']."</h2>";
            $searchPageHtml.="<div><img class='fakeimg' src='RecipeImagesUpdated/".$recID.".png'/></div>";
            $searchPageHtml.="<h5>".$row['RECDESC']."</h5>";
            $searchPageHtml.="<h5>".$servings." serving(s) - Time: ".$row['TOTALTIME']." minute(s)</h5>";
            $searchPageHtml.="<h5>".$row['AVERAGERATING']."/5</h5>";
            $searchPageHtml.="<h6>".$row['TOTALREVIEWS']." reviews</h6>";
            $searchPageHtml.="<a class='nav-link active' href='RecipePage.php?id=".$recID."'>See Recipe!</a>";
            $searchPageHtml.="<br/><hr>";
    }

    if($noRecipe){
      $searchPageHtml.="<h2>No Recipe with '$ingredient' as an Ingredient!</h2>";
    }
    $searchPageHtml.="<hr class='d-sm-none'>
          </div>
        </div>
      </div>
    </div>";
    //==============================================

    // End of Page
    //==============================================
    $searchPageHtml.="<div class='jumbotron text-center' style='margin-bottom:0'>
      <p>Copyrighted by Mathieu Trudeau, Keen Cooks Inc. 2019</p>
    </div>
    </body>
    </html>";
    //==============================================

    echo $searchPageHtml;
}
else
{
    echo "<p>Something went wrong...</p>";
}

oci_close($conn);
?>