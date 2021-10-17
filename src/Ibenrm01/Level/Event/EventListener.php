<?php

namespace Ibenrm01\Level\Event;

use pocketmine\{
    Server, Player
};
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\level\{
    Level, Position
};
use pocketmine\event\player\{
    PlayerJoinEvent
};
use pocketmine\event\block\{
    BlockBreakEvent
};
use onebone\economyapi\EconomyAPI;
use Ibenrm01\Level\Main;

class EventListener implements Listener {

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $this->plugin->register($player);
        sleep(1);
        $this->plugin->onTags($player);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if($this->plugin->getConfig()->get("private-world") != true){
            foreach($this->plugin->getConfig()->getAll()['block-list'] as $list){
                $values = explode(":", $list);
                if($block->getId() == $values[0] && $block->getDamage() == $values[1]){
                    if($this->plugin->data[$player->getName()]['settings']['notice-progress'] == "ON"){
                        $this->plugin->addProgress($player, $player->getName(), $values[2], "SEND");
                        $player->sendPopup($this->plugin->replace($this->plugin->lang->get("notice.progress"), [
                            "progress" => $values[2]
                        ]));
                        return;
                    }
                    $this->plugin->addProgress($player, $player->getName(), $values[2], "SEND");
                    return;
                }
            }
        } else {
            foreach($this->plugin->getConfig()->getAll()['world-list'] as $wd) :
                if($block->getLevel()->getName() != $wd){
                    return;
                }
            endforeach;
                foreach($this->plugin->getConfig()->getAll()['block-list'] as $list){
                    $values = explode(":", $list);
                    if($block->getId() == $values[0] && $block->getDamage() == $values[1]){
                        if($this->plugin->data[$player->getName()]['settings']['notice-progress'] == "ON"){
                            $this->plugin->addProgress($player, $player->getName(), $values[2], "SEND");
                            $player->sendPopup($this->plugin->replace($this->plugin->lang->get("notice.progress"), [
                                "progress" => $values[2]
                            ]));
                            return;
                        }
                        $this->plugin->addProgress($player, $player->getName(), $values[2], "SEND");
                        return;
                    }
                }
        }
    }
}