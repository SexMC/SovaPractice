<?php
namespace sova\translation;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use sova\Loader;

class Translation{
    static array $messages = [];
    public static function init(Loader $plugin): void{
        $config = new Config($plugin->getDataFolder() . "messages.yml", Config::YAML);

        self::$messages = $config->getAll();
    }

    public static function translate(string $key, array $params = []): string{
        $message = self::$messages[$key];
        foreach($params as $key => $value){
            $message = str_replace("%" . $key . "%", $value, $message);
        }
        return TextFormat::colorize($message);
    }
}