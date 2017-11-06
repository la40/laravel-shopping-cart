<?php

    return [
        /**
         *  Class representing the items
         */
        "item_class" => \App\Product::class,

        /**
         *  Prefix of the session key
         */
        "session_key" => "tY2ts241pt",

        /**
         *  The name of the cart singleton instance that will be registered in the IOC
         */
        "instance_name" => "cart",

        /**
         * TODO: Not implemented yet
         */
        /*"sync_items" => function($item)
        {
            return false;
        },*/
        "sync_items" => null,

        /**
         *  Number format, set it to false to ingore any number formating
         */
        //"number_format" => null,
        "number_format" => [
            "decimals" => 2,
            "dec_point" => ".",
            "thousands_sep" => ""
        ],
    ];