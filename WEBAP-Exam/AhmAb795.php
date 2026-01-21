<?php
$host = 'localhost';
$username = 'root';
$password = '';
$port = '3306';
$certificate_file_path = '';
$database = 'MiniWebShop';
$connection = mysqli_connect($host, $username, $password, $database);


if (isset($_POST['pageloaded']) && $_POST['pageloaded'] == true) {
    $sts = $connection->prepare("select * from Items");
    $sts->execute();
    $result = $sts->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            "itemId" => $row['itemId'],
            "itemName" => $row['itemName'],
            "itemStock" => $row['stock'],
        ];
    }
    echo json_encode($items);
}

if (isset($_POST['inputQuantity'], $_POST['selectedOption'])) {
    $stst = $connection->prepare("select * from Items where itemId = ?");
    $stst->bind_param('i', $_POST['selectedOption']);
    $stst->execute();
    $result = $stst->get_result();

    $outOfStock = false;
    if ($result->num_rows <= 0) {
        echo "out of stock";
        $outOfStock = true;
        return;
    }
    if ($outOfStock == false) {
        $updateStock = $connection->prepare("update");
    }
}
