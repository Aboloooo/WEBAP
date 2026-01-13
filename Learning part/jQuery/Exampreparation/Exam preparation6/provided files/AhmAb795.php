 <?php
    $host = "localhost";
    $username = "root";
    $pass = "";
    $db = "fruits";

    $connection = mysqli_connect($host, $username, $pass, $db);


    if (isset($_POST['pageLoadedAll']) && $_POST['pageLoadedAll'] == true) {
        $fruitsExtractionInfo = $connection->prepare("SELECT * FROM fruits");
        $fruitsExtractionInfo->execute();
        $result = $fruitsExtractionInfo->get_result();

        $fruits = [];
        while ($row = $result->fetch_assoc()) {
            /* appending to the array $fruits */
            $fruits[] = [
                "fruitId" => $row['fruitId'],
                "fruitName" => $row['fruitName']
            ];
        }
        echo json_encode($fruits);
    }


    /* selected option */
    if (isset($_POST['optionSelected'], $_POST['selectedOptionValue'])) {
        $productQuantity = $connection->prepare("SELECT availability FROM fruits where fruitId = ?");
        $productQuantity->bind_param('i', $_POST['selectedOptionValue']);
        $productQuantity->execute();
        $resultQ = $productQuantity->get_result();
        $row = $resultQ->fetch_assoc();
        $availableQuantity = ($row['availability'] > 0) ? "Quantity available: " . $row['availability'] : "Product out of stock";
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