<?php
include ("functions.php");
include ('Classes/DB.php');
include ('Classes/Authenticate.php');
include ("Classes/Categories.php");
include ("Classes/Lots.php");
include ("Classes/Binds.php");
include ("Classes/Templates.php");
// установка и проверка устновки соединения с бд
session_start();
DB::getConnection();
$user = new Authenticate();
//Заполняем данные для шаблона header
$categories = Categories::getAll();

$header_data = $user->getAuthorizedData();
$header_data["categories_equipment"] =$categories;
//заполняем данные для шаблона main
$data = $user->getAuthorizedData();
$data["categories_equipment"] = $categories;
//заполняем данные для шаблона footer
$data_footer["categories_equipment"] = $categories;


// проверка пришел ли id лота и получение данных о лоте  из базы
$lot_item = "";
$bets = "";
$lot_id = $_REQUEST["id"];

$data["bind_done"] = false;
if ($user->getAuthorizedData()) {
    $data["bind_done"] = Binds::isPutAllowed($lot_id, $user->getAuthorizedData("id"));
}

if (!empty($lot_id) && is_numeric($lot_id)) {
    $lot_item = Lots::getByKey("id",$lot_id);
    $lot_bets = Binds::getByLotID($lot_id);
}

if ($lot_item == "") {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 Страница не найдена</h1>";
    exit ();
} else {
    //подготовка данных их базы для шаблона.
    $data["lot_item"] = $lot_item;
    //Получение данных о ставках для лота из базы
    $data["bets"] = $lot_bets;
}

if (isset($_POST["send"])) {
    $time  = time();
    $lotFields = ['cost', 'id'];
    $error = [];
    $form_item = [];
    foreach ($lotFields as $key) {
        if (!empty($_POST[$key]) || $_POST[$key] === "0") {
            $form_item[$key] = htmlspecialchars($_POST[$key]);
        } else {
            $error[$key] = "Заполните ставку";
        }
    }
    if (!$error) {
        $maxBet = Binds::getMax($form_item["id"]);
        if (!is_numeric($form_item['cost'])) {
            $error['cost'] = "Заполните ставку в виде числа";
        } else if ((int)$form_item['cost'] < $maxBet) {
            $error['cost'] = "Ставка должна быть больше ".$maxBet;
        } else {
            $data = array (
                "user_id" => $user->getAuthorizedData("id"),
                "lot_id" => $form_item["id"],
                "cost" => $form_item["cost"]
            );

            $result = Binds::addNew($data);

            header("Location: /mylots.php");
            exit();
        }
    }
    if ($error) {
        $data['error'] = $error;
        echo Templates::render("templates/header.php", $header_data);
        echo Templates::render("templates/main-lot.php", $data);
        echo Templates::render("templates/footer.php", $data_footer);
    }
    // блок else не нужен, т.к. если никаких ошибок не было найдено, то выполнение скрипта завершится раньше.
} else {
    $data ["error"] = array();

    echo Templates::render("templates/header.php", $header_data);
    echo Templates::render("templates/main-lot.php", $data);
    echo Templates::render("templates/footer.php", $data_footer);
}
    ?>
