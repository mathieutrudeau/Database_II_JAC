<?php
$username="user1754757";
$password="password";
$dbname="//10.39.167.152/pdborcl";

$conn=oci_connect($username, $password, $dbname);

$pageTitle="Keen Cooks";

if($conn)
{
    // ID of the category to show
    $catID=$_GET['id'];
    
    // Page HTML.
    $categoryPageHtml="";

    // Add head of page + CSS.
    //===============================================
    $categoryPageHtml.="<!DOCTYPE html>
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
    //===============================================

    // Show the category title and description
    //===============================================
    $stmt1=oci_parse($conn, "SELECT catID, catName, catDesc FROM Categories WHERE catID=:catid");
    oci_bind_by_name($stmt1, ':catid', $catID, -1);
    oci_execute($stmt1);
    $row=oci_fetch_array($stmt1, OCI_ASSOC);

    $categoryPageHtml.="  <!-- TITLE SECTION -->
    <div class='jumbotron text-center' style='margin-bottom:0'>
      <h1>".$row['CATNAME']." Recipes</h1>
      <p>".$row['CATDESC']."</p> 
    </div>";
    //===============================================

    // NAVIGATION BAR : CATEGORIES
    //==============================================
    $categoryPageHtml.="</div>";
    $categoryPageHtml.="  <!-- NAVIGATION BAR : CATEGORIES -->
    <nav class='navbar navbar-expand-sm bg-light navbar-light'>
      <a class='navbar-brand' href='MainPage.php'>Main Page</a>
      <a class='navbar-brand' href='#'>Categories</a>
      <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapsibleNavbar'>
        <span class='navbar-toggler-icon'></span>
      </button>
      <div class='collapse navbar-collapse' id='collapsibleNavbar'>";
    
    $stid=oci_parse($conn, 'SELECT catID, catName, numberOfRecipes FROM VWCategoriesAndRecipes');
    oci_execute($stid);

    $categoryPageHtml.="<ul class='navbar-nav'>";
    while(($row=oci_fetch_array($stid, OCI_ASSOC)))
    {
        $categoryPageHtml.="<li 'nav-item'><a class='nav-link' href='CategoryPage.php?id=".$row['CATID']."'>".$row['CATNAME']." (".$row['NUMBEROFRECIPES'].")</a></li>";
    }
    $categoryPageHtml.="</ul>";
    //==============================================
      
    // NAVIGATION BAR : INGREDIENT SEARCH
    //==============================================
    $categoryPageHtml.="</div>
      <form method='POST' action='SearchResultPage.php'>
      <input type='text' name='searchIngredient' value='Search Ingredients..'>
      </form>
    </nav>";
    //==============================================
    
    // Show all recipes in that category
    //==============================================
    $categoryPageHtml.="
     <!-- RECIPES IN THAT CATEGORY -->
    <div class='container' style='margin-top:30px'>
      <div class='row'>
        <div class='col-sm-12'>";

        $stmt2=oci_parse($conn, "SELECT recID, recName, recDesc,(prepTime+CookTime) AS TotalTime, Serves, averageRating, totalReviews FROM Recipes Where recID in (Select recID From RecipesInCategories Where catID='$catID')");
        oci_execute($stmt2);

        while(($row=oci_fetch_array($stmt2, OCI_ASSOC))){
            
            $servings="N/A";
            if (count($row)==7){
                $servings=$row['SERVES'];
            }
            $recID=$row['RECID'];
            $categoryPageHtml.="<h2>".$row['RECNAME']."</h2>";
            $categoryPageHtml.="<div><img class='fakeimg' src='RecipeImagesUpdated/".$recID.".png'/></div>";
            $categoryPageHtml.="<h5>".$row['RECDESC']."</h5>";
            $categoryPageHtml.="<h5>".$servings." serving(s) - Time: ".$row['TOTALTIME']." minute(s)</h5>";
            $categoryPageHtml.="<h5>".$row['AVERAGERATING']."/5</h5>";
            $categoryPageHtml.="<h6>".$row['TOTALREVIEWS']." reviews</h6>";
            $categoryPageHtml.="<a class='nav-link active' href='RecipePage.php?id=".$recID."'>See Recipe!</a>";
            $categoryPageHtml.="<br/><hr>";
        } 
          $categoryPageHtml.="<hr class='d-sm-none'>
          </div>
        </div>
      </div>
    </div>";
    //==============================================
    
    // End of Page
    //==============================================
    $categoryPageHtml.="<div class='jumbotron text-center' style='margin-bottom:0'>
      <p>Copyrighted by Mathieu Trudeau, Keen Cooks Inc. 2019</p>
    </div>
    </body>
    </html>";
    //==============================================

    echo $categoryPageHtml;
}
else
{
    echo "<p>Something went wrong...</p>";
}

oci_close($conn);
?>