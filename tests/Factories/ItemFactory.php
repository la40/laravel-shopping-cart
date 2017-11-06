 <?php
    $config = include(__DIR__."/../Mock/config.php");
    $factory->define($config["item_class"], function (Faker\Generator $faker) {

        static $id;
        $id++;

        static $price;
        $price += 5.24;

        return [
            "id" => $id,
            "name" => $faker->name,
            "price" => $price
        ];
    });