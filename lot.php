<?php

include ('autoload.php');

session_start();
//проверка авторизации и получение данных
$user_data = Authorization::getAuthData();

$categories = CategoryFinder::getAll();
//заполняем данные для шаблона header
$header_data["user"] = $user_data;
$header_data["categories_equipment"] = $categories;
//заполняем данные для шаблона main
$data["user"] = $user_data;
$data["categories_equipment"] = $categories;
//заполняем данные для шаблона footer
$data_footer["categories_equipment"] = $categories;


// проверка пришел ли id лота и получение данных о лоте  из базы
$lot_item = null;
$lot_bets = [];
$bets = null;
$lot_id = protectXSS($_REQUEST["id"]);

$data["can_make_bet"] = false;

if ($user_data) {
    $data["can_make_bet"] = BindFinder::canMakeBet($lot_id, $user_data->id);

}

if (!empty($lot_id) && is_numeric($lot_id)) {
    $lot_item = LotFinder::getById($lot_id);
    $lot_bets = BindFinder::getByLotID($lot_id);
}

if ($lot_item ===  null) {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 Страница не найдена</h1>";
    exit ();
} else {
    //подготовка данных их базы для шаблона.
    $data["lot_item"] = $lot_item;
    //Получение данных о ставках для лота из базы
    $data["bets"] = $lot_bets;
}

if (isset($_POST["AddBindForm"])) {
    $time  = time();
    $form = AddBindForm::getFormData();

    if ($form->isValid()) {
        $lot_item = LotFinder::getById($form->lot_id);
        $maxBet = $lot_item->getMinNextBet();
        if ((int)$form->price < $maxBet) {
            $error['price'] = "Ставка должна быть больше ".$maxBet;
        } else {
            $data = array (
                "user_id" => $user_data->id,
                "lot_id" => (int)$form->lot_id,
                "price" => (int)$form->price,
                "date" => date("Y:m:d H:i:s")
            );

            $b = new Bind($data);
            $b->insert();
            header("Location: /lot.php?id=".$b->lot_id);
            exit();
        }
    } else {
        $error = $form->getErrors();
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
