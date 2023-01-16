<?php
$username="user1754757";
$password="password";
$dbname="//10.39.167.152/pdborcl";

$conn=oci_connect($username, $password, $dbname);

$pageTitle="Keen Cooks";



if($conn)
{
    // Page HTML.
    $mainPageHtml="";

    // Add head of page + CSS.
    //==============================================
    $mainPageHtml.="<!DOCTYPE html>
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
      </style>
    </head>
    <body>";
    //==============================================

    # TITLE SECTION
    //==============================================
    $mainPageHtml.="<!-- TITLE SECTION -->
    <div class='jumbotron text-center' style='margin-bottom:0'>
      <h1>Keen Cooks</h1>
      <p>The place where recipes come true!</p> 
    </div>";

    // NAVIGATION BAR : CATEGORIES
    //==============================================
    $mainPageHtml.="  <!-- NAVIGATION BAR : CATEGORIES -->
    <nav class='navbar navbar-expand-sm bg-light navbar-light'>
      <a class='navbar-brand' href='#'>Categories</a>
      <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapsibleNavbar'>
        <span class='navbar-toggler-icon'></span>
      </button>
      <div class='collapse navbar-collapse' id='collapsibleNavbar'>";
    $stid=oci_parse($conn, 'SELECT catID, catName, numberOfRecipes FROM VWCategoriesAndRecipes');
    oci_execute($stid);

    $mainPageHtml.="<ul class='navbar-nav'>";
    while(($row=oci_fetch_array($stid, OCI_ASSOC)))
    {
        $mainPageHtml.="<li 'nav-item'><a class='nav-link' href='CategoryPage.php?id=".$row['CATID']."'>".$row['CATNAME']." (".$row['NUMBEROFRECIPES'].")</a></li>";
    }
    $mainPageHtml.="</ul>";
    //==============================================
      
    # NAVIGATION BAR : INGREDIENT SEARCH
    //==============================================
    $mainPageHtml.="</div>
      <form method='POST' action='SearchResultPage.php'>
      <input type='text' name='searchIngredient' value='Search Ingredients..'>
      </form>
    </nav>";
    //==============================================

    # RECIPE OF THE DAY + TEMP CONVERTER
    //==============================================
    // Recipe of the day
    $mainPageHtml.="<!-- RECIPE OF THE DAY + TEMP CONVERTER -->
    <div class='container' style='margin-top:30px'>
      <div class='row'>
        <div class='col-sm-7'>";

    $stmt1=oci_parse($conn, "SELECT RecID, RecName, RecDesc, (prepTime+CookTime) AS TotalTime, Serves FROM Recipes WHERE recID=(SELECT ChooseRandomRecipe FROM Dual)");
    oci_execute($stmt1);
    $row=oci_fetch_array($stmt1, OCI_ASSOC);

    $servings="N/A";
    if (count($row)==5){
        $servings=$row['SERVES'];
    }

    $mainPageHtml.="<h2>Recipe of the Day!</h2>
          <h5>".$row['RECNAME']."</h5>
          <div><img class='fakeimg' src='RecipeImagesUpdated/".$row['RECID'].".png'/></div>
          <p>".$row['RECDESC']."</p>
          <h6>".$servings." serving(s) - Time: ".$row['TOTALTIME']." minute(s)</h6>
          <a class='nav-link active' href='RecipePage.php?id=".$row['RECID']."'>See Recipe!</a>
          <hr class='d-sm-none'>
          <br/>";
    //==============================================

    // Temperature Converter
    //==============================================
    // Set Values
    $tempVal=0;
    $inTemp="C";
    $tempTypes=array("C", "F", "G");
    $temps=array();
  
    if(isset($_POST['inTemp'])&&isset($_POST['temp'])){
      $inTemp=$_POST['inTemp'];
      $tempVal=$_POST['temp'];
    }

    // Get values from database
    for($i=0; $i<3; $i++){
    $stmt2=oci_parse($conn, "SELECT ConvertOvenTemp(:val,:inVal,:outVal) as Temp From Dual");
    oci_bind_by_name($stmt2, ':val', $tempVal, -1);
    oci_bind_by_name($stmt2, ':inVal', $inTemp, -1);
    oci_bind_by_name($stmt2, ':outVal', $tempTypes[$i], -1);
    oci_execute($stmt2);
    $row=oci_fetch_array($stmt2, OCI_ASSOC);
    if($tempTypes[$i]=="C"){
    array_push($temps, round($row['TEMP'],2));
    }
    else{
      array_push($temps, $row['TEMP']);
    }
    }

    //Show the form
    $mainPageHtml.="<h3>Temperature Converter</h3>
          <form method='POST' action='MainPage.php'>
          <input type='text' name='temp' value='0'>
           °C <input type='radio' name='inTemp' group='inTemp' value='C'>
           F <input type='radio' name='inTemp' group='inTemp' value='F'>
           Gas Mark <input type='radio' name='inTemp' group='inTemp' value='G'>
            </form>
          <ul class='nav nav-pills flex-column'>
            <li class='nav-item'>Celcius: ".$temps[0]."</li>
            <li class='nav-item'>Fahrenheit: ".$temps[1]."</li>
            <li class='nav-item'>Gas Mark: ".$temps[2]."</li>
          </ul><br/>";
          //==============================================

        // Show All category descriptions
        //==============================================
        oci_execute($stid);
        while(($row=oci_fetch_array($stid, OCI_ASSOC)))
        {
          $mainPageHtml.="<hr/><h4><a class='nav-link' href='CategoryPage.php?id=".$row['CATID']."'>".$row['CATNAME']." -".$row['NUMBEROFRECIPES']." recipe(s)</a></h4>";
          $stmtcat=oci_parse($conn, "SELECT catID, catName, catDesc FROM Categories WHERE catID=:catid");
          oci_bind_by_name($stmtcat, ':catid', $row['CATID'], -1);
          oci_execute($stmtcat);
          $row2=oci_fetch_array($stmtcat, OCI_ASSOC);
          $mainPageHtml.="<p>".$row2['CATDESC']."</p>";
          oci_free_statement($stmtcat);
        }
          
        $mainPageHtml.="</div>";
        //==============================================


        # Special recipes section: Quick, Easy & Popular recipes
        //==============================================
        $mainPageHtml.="<!-- Special recipes section: Quick, Easy & Popular recipes -->
        <div class='col-sm-5'>
          <h3>Quick Recipes!</h3>
          <p>Food will be on the table under one hour.</p>
          <hr/><ul class='nav nav-pills flex-column'>";
            
          // Quick recipes
        $stid=oci_parse($conn, 'SELECT recID, RecName, TotalTime FROM vwOneHourOrLess');
        oci_execute($stid);
        while(($row=oci_fetch_array($stid, OCI_ASSOC))){
            $mainPageHtml.="<li class='nav-item'>
                <a class='nav-link' href='RecipePage.php?id=".$row['RECID']."'>".$row['RECNAME']."<br/>Time: ".$row['TOTALTIME']." minute(s) </a>
                </li>";
        }

        $mainPageHtml.="</ul><br/>
          <h3>Easy Recipes!</h3>
          <p>Fewer ingredients doesn't mean fewer flavours.</p><hr/>
          <ul class='nav nav-pills flex-column'>";
        
          // Easy recipes
          $stid=oci_parse($conn, 'SELECT recID, RecName, NumberOfIngredients FROM vwfiveingredientsorless');
          oci_execute($stid);
          while(($row=oci_fetch_array($stid, OCI_ASSOC))){
              $mainPageHtml.="<li class='nav-item'>
                  <a class='nav-link' href='RecipePage.php?id=".$row['RECID']."'>".$row['RECNAME']."<br/>".$row['NUMBEROFINGREDIENTS']." ingredient(s) </a>
                  </li>";
          }

        $mainPageHtml.="</ul>
          <h3>Popular Recipes!</h3>
          <p>Recipes that made it to the top 3.</p>
          <hr/><ul class='nav nav-pills flex-column'>";
          
          // Popular recipes
          $stid=oci_parse($conn, 'SELECT recID, RecName, AverageRating, TotalReviews FROM Recipes ORDER BY AverageRating DESC FETCH FIRST 3 ROWS ONLY');
          oci_execute($stid);
          while(($row=oci_fetch_array($stid, OCI_ASSOC))){
              $mainPageHtml.="<li class='nav-item'>
                  <a class='nav-link' href='RecipePage.php?id=".$row['RECID']."'>".$row['RECNAME']."<br/>Rating: ".$row['AVERAGERATING']." (".$row['TOTALREVIEWS'].")</a>
                  </li>";
          }

        $mainPageHtml.="</ul>
        </div>
      </div>
    </div>";
    //==============================================

    // End of Page
    //==============================================
        $mainPageHtml.="<div class='jumbotron text-center' style='margin-bottom:0'>
      <p>Copyrighted by Mathieu Trudeau, Keen Cooks Inc. 2019</p>
      </div>
      </body>
      </html>";
      //==============================================

      echo $mainPageHtml;
}
else
{
    echo "<p>Something went wrong...</p>";
}



oci_close($conn);
?>