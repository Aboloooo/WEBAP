 <?php
    $host = "localhost";
    $username = "root";
    $pass = "";
    $db = "fruits";

    $connection = mysqli_connect($host, $username, $pass, $db);
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
         <?php
            $output = null;
            $fruitsExtractionInfo = $connection->prepare("SELECT * FROM fruits");
            $fruitsExtractionInfo->execute();
            $result = $fruitsExtractionInfo->get_result();
            while ($row = $result->fetch_assoc()) {
                $fruitName = $row['fruitName'];
                $fruitId = $row['fruitId'];
            ?>
             <option value="<?= $fruitId ?>"><?= $fruitName ?></option>
         <?php
            }
            if (isset($_POST['optionSelected'], $_POST['selectedOptionValue'])) {
                $productQuantity = $connection->prepare("SELECT availability FROM fruits where fruitId = ?");
                $productQuantity->bind_param('i', $_POST['selectedOptionValue']);
                $productQuantity->execute();
                $resultQ = $productQuantity->get_result();
                $row = $resultQ->fetch_assoc();
                $availableQuantity = $row['availability'];
                if ($row && $availableQuantity > 0) {
                    echo json_encode([
                        "Quantity" => $availableQuantity
                    ]);
                    exit;
                } else {
                    echo "Target product not exits";
                }
            }
            ?>
     </select>
     <div id="FruitData"></div>
     <div id="FruitOrder"></div>
     <div id="OrderResult"></div>
 </body>

 </html>