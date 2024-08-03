<?php
require_once 'backend/sdbh.php'; // подключение PHP файла с классом для работы с БД
$dbh = new sdbh(); // подключение класса для работы с БД
$checkBoxNumber = 0; // ID для чекбоксов
$autoNum = 0; // ID текущего автомобиля
$servicesPriceSum = 0; // начальная сумма сервисов
$sum = 0; // начальная сумма всех услуг
$productsPrice = $_GET['product']; // запрос с поля product
$days = $_GET['days']; // запрос с поля дни
$servicesPrice = $_GET['checks']; // запрос с поля сервисов

$productsInfo = $dbh->make_query('SELECT * FROM `a25_products`');
// вычисление стоимости автомобиля и сервисов
if (isset($_GET['product'])) {
    foreach ($productsInfo as $key => $value) {
        if ($value["PRICE"] == $productsPrice) {
            $carName = $value["NAME"];
        }
    }
    $autoTarif = unserialize($dbh->mselect_rows('a25_products', ['NAME' => $carName], 0, 1, 'id')[0]['TARIFF']);
    if ($autoTarif) {
        foreach ($autoTarif as $daysByTarif => $tarifPrice) {
            if ($days >= $daysByTarif) {
                $productsPrice = $tarifPrice;
            }
        }
    }
    if (isset($servicesPrice)) {
        foreach ($servicesPrice as $key => $value) {
            $servicesPriceSum += $value;
        }
    }
    $sum = ($servicesPriceSum + $productsPrice) * $days;
}
?>
<!DOCTYPE html>

<head>
    <meta charset="utf-8" />
    <title>Тестовое A25</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/style_form.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="row row-header">
            <div class="col-12">
                <img src="assets/img/logo.png" alt="logo" style="max-height:50px" />
                <h1>Прокат</h1>
            </div>
        </div>
        <div class="row row-form">
            <div class="col-12">
                <div class="container" style="padding: 0 12px;">
                    <div class="row row-body">
                        <div class="col-3">
                            <span style="text-align: center">Форма расчета тарифа</span>
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <div class="col-9">
                            <form id="form" name="form">
                                <label class="form-label" for="product">Выберите продукт:</label>
                                <select class="form-select" name="product" id="product">
                                    <!-- Вывод данных по автомобилям -->
                                    <?php
                                    foreach ($productsInfo as $key => $name) { ?>
                                        <option value="<?= ($name["PRICE"]); ?>"><?= ($name["NAME"]); ?> за <?= ($name["PRICE"]); ?> (цена без тарифа)</option>
                                    <? } ?>
                                </select>
                                <label for="customRange1" class="form-label">Количество дней:</label>
                                <input type="text" class="form-control" id="customRange1" min="1" max="30" name="days" value="1">
                                <label for="customRange1" class="form-label">Дополнительно:</label>
                                <!-- Вывод данных по сервисам -->
                                <?
                                $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                                foreach ($services as $k => $s) {
                                    $checkBoxNumber++;
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="<?= $s ?>" id="flexCheckChecked<?= $checkBoxNumber ?>" name="checks[]">
                                        <label class="form-check-label" for="flexCheckChecked<?= $checkBoxNumber ?>">
                                            <?= $k ?> за <?= $s ?>
                                        </label>
                                    </div>
                                <? } ?>
                                <button type="submit" class="btn btn-primary">Рассчитать</button> <br />
                                *Сумма изменяется в зависимости от количества дней: <br />
                                <!-- Вывод информации о тарифах -->
                                <?php
                                $productsName = $dbh->make_query('SELECT NAME FROM `a25_products`');
                                foreach ($productsName as $key => $value) {
                                    $autoTarif[$autoNum] = unserialize($dbh->mselect_rows('a25_products', ['NAME' => $value["NAME"]], 0, 1, 'id')[0]['TARIFF']);
                                    if ($autoTarif[$autoNum]) {
                                        echo ($value["NAME"] . " будет стоить: ");
                                        foreach ($autoTarif[$autoNum] as $days => $tarif) {
                                            echo ($days . " и более дней - " . $tarif . "<br>");
                                        }
                                    }
                                    $autoNum++;
                                }
                                ?>
                            </form>
                            <!-- Вывод суммы, в случае, если она есть -->
                            <?php
                            if ($productsPrice) {
                                echo '<p class="text-center" style="font-weight: 600; font-size: 24px; color:green;">Сумма: ' . $sum . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>