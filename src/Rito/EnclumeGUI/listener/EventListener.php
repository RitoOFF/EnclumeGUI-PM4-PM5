<?php

namespace Rito\EnclumeGUI\listener;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use onebone\economyapi\EconomyAPI;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;
use Rito\EnclumeGUI\Main;

class EventListener implements Listener{
    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $itemhand = $player->getInventory()->getItemInHand();
        if ($event->getBlock()->getTypeId() === VanillaBlocks::ANVIL()->getTypeId()){
            if ($itemhand instanceof Tool | $itemhand instanceof Armor){
                $menu = InvMenu::create(InvMenu::TYPE_CHEST);
                $inventory = $menu->getInventory();
                $inventory->setItem(12, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem()->setCustomName("§r§cAnnuler")->setLore(["Click pour fermer l'enclume"]));
                $inventory->setItem(14, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem()->setCustomName("§r§aValider")->setLore(["Click pour repair votre item"]));

                $menu->setListener(function (InvMenuTransaction $transaction) use ($menu, $itemhand): InvMenuTransactionResult {
                    $item = $transaction->getItemClicked();
                    return $transaction->discard()->then(function (Player $player) use ($item, $menu, $itemhand) {
                        $config = Main::getInstance()->getConfig();
                        if ($item->getName() === "§r§aValider") {
                            if ($config->get("economyapi") === true) {
                                if (EconomyAPI::getInstance()->myMoney($player) >= $config->get("prix.repair-money")) {
                                    $player->removeCurrentWindow();
                                    EconomyAPI::getInstance()->reduceMoney($player, $config->get("prix.repair-money"));

                                    $itemhand->setDamage(0);
                                    $player->getInventory()->setItemInHand($itemhand);
                                    $player->broadcastSound(new AnvilUseSound());
                                } else {
                                    $player->sendMessage($config->get("message-nomoney"));
                                }
                            }elseif ($config->get("xp-enclume") === true) {
                                if ($player->getXpManager()->getXpLevel() === $config->get("prix.repair-xp")){
                                    $player->removeCurrentWindow();
                                    $player->getXpManager()->subtractXpLevels($config->get("prix.repair-xp"));
                                    $itemhand->setDamage(0);
                                    $player->getInventory()->setItemInHand($itemhand);
                                    $player->broadcastSound(new AnvilUseSound());
                                }else {
                                    $player->sendMessage($config->get("message-noxp"));
                                }
                            }
                        }
                        if ($item->getName() === "§r§cAnnuler") {
                            $player->removeCurrentWindow();
                        }

                    });
                });
                $menu->setName("Enclume");
                $menu->send($player);
            }else{
                $player->sendMessage(Main::getInstance()->getConfig()->get("message.no-item"));
            }

        }
    }
}