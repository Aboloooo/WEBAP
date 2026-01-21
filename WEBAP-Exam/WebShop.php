 <?php
    include_once("./AhmAb795.php");
    ?>
 <!doctype html>
 <html lang="en">

 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
     <script src="jquery-3.7.1.min.js"></script>
     <script src="myShop.js?t=<?= time(); ?>"></script>
     <title>Document</title>
     <style>
         .myMessages {
             background-color: bisque;
             height: 500px;
             border: 5px solid gray;
         }
     </style>
 </head>

 <body>
     <select id="itemList"></select>
     <input id="Quantity" placeholder="How many" />
     <button id="Order">Place order</button>
     <div id="Messages"></div>
     <div id="items">
         <div id="orders"></div>
 </body>

 </html>