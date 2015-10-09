<?php
$config_server = new \Phalcon\Config(array(
    'api' => array(
        'yandex_disk' => array(
            'client_id' => '',
            'client_secret' => '',
            'ya_token' => '',
            'base_dir' => ''
        ),
        'telegram' => array(
            'api_key' => "",
            "chat_ids" => array(1)
        )
    )
));