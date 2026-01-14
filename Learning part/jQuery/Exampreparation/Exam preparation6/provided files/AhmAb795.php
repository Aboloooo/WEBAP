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
        $productQuantity = $connection->prepare("SELECT fruitId,availability FROM fruits where fruitId = ?");
        $productQuantity->bind_param('i', $_POST['selectedOptionValue']);
        $productQuantity->execute();
        $resultQ = $productQuantity->get_result();
        $row = $resultQ->fetch_assoc();
        $productId = $row['fruitId'];
        $availableQuantity = $row['availability'];
        if ($row) {
            echo json_encode([
                "ID" => $productId,
                "Quantity" => $availableQuantity
            ]);
            exit;
        } else {
            echo "Target product not exits";
        }
    }

    if (isset($_POST['btnClicked'], $_POST['ProductId'], $_POST['newQuantity'])) {
        $orderUpdate = $connection->prepare("UPDATE fruits set availability = ? where fruitId = ?");
        $orderUpdate->bind_param('ii', $_POST['newQuantity'], $_POST['ProductId']);
        $orderUpdate->execute();
    }
    ?>