<?php
require __DIR__ . '/vendor/autoload.php';
error_reporting(E_ALL);
session_start();

$yandexAPI = new \Yandex\Geo\Api();
if(!empty($_GET['clear'])) {
  header('Location: index.php');
  die();
}

if (!empty($_GET['search'])) {
  $addr = $_GET['address'];
  $yandexAPI->setQuery($addr);
  $yandexAPI->setLang(\Yandex\Geo\Api::LANG_RU);
  $yandexAPI->load();
  
  $response = $yandexAPI->getResponse();
  $collection = $response->getList();
  foreach ($collection as $item) {
    $long = $item->getLongitude();
    $lat = $item->getLatitude();
    $address = $item->getAddress();
    $locationArray[] = [
      'link' => 'lat=' . $lat . '&long=' . $long,
      'exact_address' => $address
    ];
  }
}

$lat = !empty($_GET['lat']) ? (float)$_GET['lat'] : 55.76;
$long = !empty($_GET['long']) ? (float)$_GET['long'] : 37.64;
$address = !empty($_GET['exact_address']) ? $_GET['exact_address'] : 'Москва';

?>

<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
  <script type="text/javascript">
      ymaps.ready(init);
      var myMap,
          myPlacemark,
          myLat = <?= $lat ?>,
          myLong = <?= $long ?>,
          myAddress = '<?= $address ?>';
      
      function init(){
          myMap = new ymaps.Map("map", {
              center: [myLat, myLong],
              zoom: 7
          });

          myPlacemark = new ymaps.Placemark([myLat, myLong], {
              hintContent: myAddress,
              balloonContent: myAddress
          });

          myMap.geoObjects.add(myPlacemark);
      }
  </script>

  <title>Карта</title>
</head>
<body>
  <div id="map" style="width: 600px; height: 400px"></div>
  <form method="get">
    <label for="address">Введите адрес:</label>
    <input type="text" name="address" id="address">
    <input type="submit" value="Найти на карте" name="search">
    <input type="submit" value="Очистить результат" name="clear">
  </form>
  <br>
  <?php
    if (!empty($locationArray)) {
      $addr = str_replace(' ', '+', $addr);
      $uri = 'index.php?address=' . $addr . '&search=Найти+на+карте&';
      foreach ($locationArray as $location) {
        $selectedAddress = $location['link'] . '&exact_address=' .  $location['exact_address'];
  ?>
        <a href="<?= $uri . $selectedAddress ?>"><?= $location['exact_address'] ?></a>
        <br>
  <?php
      }
    }
  ?>
</body>
</html>