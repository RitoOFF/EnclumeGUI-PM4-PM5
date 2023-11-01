<?php

namespace Rito\EnclumeGUI;


use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Rito\EnclumeGUI\listener\EventListener;

class Main extends PluginBase{
    public static Config $config;

    use SingletonTrait;

    protected function onLoad(): void {
        self::setInstance($this);
    }


    public function onEnable(): void
    {
        $this->getResource("config.yml");
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getLogger()->notice("Enable -> EnclumeGUI Plugin BY RITO | disocrd: rito.off");
    }
    public function onDisable(): void
    {
        $this->getLogger()->notice("Disable -> EnclumeGUI Plugin BY RITO | disocrd: rito.off");
    }
}