 <?php
    include_once("./AhmAb795.php")
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
     <script src="jquery-3.6.3.min.js"></script>
     <script src="frontEnd.js?<?= time(); ?>"></script>

     <title>Fruits database</title>
 </head>

 <body>
     <!-- if select option isset then check for availbility -->
     <div id="statusDb" class="box redBox"></div>
     <select id="Fruits">
         <option>Fill this in</option>
     </select>
     <div id="FruitData"></div>
     <div id="FruitOrder"></div>
     <div id="OrderResult"></div>
 </body>

 </html>