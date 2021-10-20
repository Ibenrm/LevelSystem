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
    BlockBreakEvent, BlockPlaceEvent
};
use Ibenrm01\Level\Main;

use onebone\economyapi\EconomyAPI;
use onebone\economyland\EconomyLand;
use MihaiChirculete\WorldGuard\WorldGuard;
use MyPlot\MyPlot;

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
     * @param BlockPlaceEvent $event
     */
    public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $xg = $block->x;
        $zg = $block->z;
        if ($xg < 0){
            $xg = ($xg + 1);
        }
        if ($zg < 0){
            $zg = ($zg + 1);
        }
        $position = new Position($xg,$block->y,$zg,$block->getLevel());
        if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
            if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                    $plot = $mp->getPlotByPosition($block);
                    if (($region = $wg->getRegionFromPosition($position)) !== ""){
                        if ($region->getFlag("pluginbypass") === "false"){
                            if ($region->getFlag("block-place") === "false"){
                                if($player->hasPermission("worldguard.place." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-place." . $region->getName())){
                                    $this->addProgress($event);
                                } elseif($player->hasPermission("worldguard.build-bypass")){
                                    $this->addProgress($event);
                                } else {
                                    $player->sendMessage("§cThis Lands Protect WorldGuard");
                                    $event->setCancelled();
                                }
                            } else {
                                $this->addProgress($event);
                            }
                        }
                    } elseif($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    } elseif($info === -1){
                        $this->addProgress($event);
                    } elseif($info !== true){
                        if($info['owner'] == $player->getName()){
                            $this->addProgress($event);
                        } elseif($player->isOp()){
                            $this->addProgress($event);
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect EconomoyLand");
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                        if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                            $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                            $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                            if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                if ($region->getFlag("pluginbypass") === "false"){
                                    if ($region->getFlag("block-place") === "false"){
                                        if($player->hasPermission("worldguard.place." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-place." . $region->getName())){
                                            $this->addProgress($event);
                                        } elseif($player->hasPermission("worldguard.build-bypass")){
                                            $this->addProgress($event);
                                        } else {
                                            $player->sendMessage("§cThis Lands Protect WorldGuard");
                                            $event->setCancelled();
                                        }
                                    } else {
                                        $this->addProgress($event);
                                    }
                                }
                            } elseif($info === -1){
                                $this->addProgress($event);
                            } elseif($info !== true){
                                if($info['owner'] == $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect EconomoyLand");
                                }
                            }
                        } else {
                            if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                                $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                                if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                    if ($region->getFlag("pluginbypass") === "false"){
                                        if ($region->getFlag("block-place") === "false"){
                                            if($player->hasPermission("worldguard.place." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-place." . $region->getName())){
                                                $this->addProgress($event);
                                            } elseif($player->hasPermission("worldguard.build-bypass")){
                                                $this->addProgress($event);
                                            } else {
                                                $player->sendMessage("§cThis Lands Protect WorldGuard");
                                                $event->setCancelled();
                                            }
                                        } else {
                                            $this->addProgress($event);
                                        }
                                    }
                                }
                            } else {
                                $this->addProgress($event);
                            }
                        }
                    } else {
                        if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                            $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                            if($info === -1){
                                $this->addProgress($event);
                            } elseif($info !== true){
                                if($info['owner'] == $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect EconomoyLand");
                                }
                            }
                        } else {
                            $this->addProgress($event);
                        }
                    }
                }
            } else {
                if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                    if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                        $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                        $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                        $plot = $mp->getPlotByPosition($block);
                        if (($region = $wg->getRegionFromPosition($position)) !== ""){
                            if ($region->getFlag("pluginbypass") === "false"){
                                if ($region->getFlag("block-place") === "false"){
                                    if($player->hasPermission("worldguard.place." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-place." . $region->getName())){
                                        $this->addProgress($event);
                                    } elseif($player->hasPermission("worldguard.build-bypass")){
                                        $this->addProgress($event);
                                    } else {
                                        $player->sendMessage("§cThis Lands Protect WorldGuard");
                                        $event->setCancelled();
                                    }
                                } else {
                                    $this->addProgress($event);
                                }
                            }
                        } elseif($plot !== null){
                            if($plot->owner != ""){
                                if($plot->owner === $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect MyPlot");
                                }
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        }
                    } else {
                        if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                            $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                            if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                if ($region->getFlag("pluginbypass") === "false"){
                                    if ($region->getFlag("block-place") === "false"){
                                        if($player->hasPermission("worldguard.place." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-place." . $region->getName())){
                                            $this->addProgress($event);
                                        } elseif($player->hasPermission("worldguard.build-bypass")){
                                            $this->addProgress($event);
                                        } else {
                                            $player->sendMessage("§cThis Lands Protect WorldGuard");
                                            $event->setCancelled();
                                        }
                                    } else {
                                        $this->addProgress($event);
                                    }
                                }
                            }
                        } else {
                            $this->addProgress($event);
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                        $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                        $plot = $mp->getPlotByPosition($block);
                        if($plot !== null){
                            if($plot->owner != ""){
                                if($plot->owner === $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect MyPlot");
                                }
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        }
                    } else {
                        $this->addProgress($event);
                    }
                }
            }
        } else {
            if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                    $plot = $mp->getPlotByPosition($block);
                    if($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    } elseif($info === -1){
                        $this->addProgress($event);
                    } elseif($info !== true){
                        if($info['owner'] == $player->getName()){
                            $this->addProgress($event);
                        } elseif($player->isOp()){
                            $this->addProgress($event);
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect EconomoyLand");
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                        $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                        if($info === -1){
                            $this->addProgress($event);
                        } elseif($info !== true){
                            if($info['owner'] == $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect EconomoyLand");
                            }
                        }
                    } else {
                        $this->addProgress($event);
                    }
                }
            } else {
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $plot = $mp->getPlotByPosition($block);
                    if($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    }
                } else {
                    $this->addProgress($event);
                }
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $xg = $block->x;
        $zg = $block->z;
        if ($xg < 0){
            $xg = ($xg + 1);
        }
        if ($zg < 0){
            $zg = ($zg + 1);
        }
        $position = new Position($xg,$block->y,$zg,$block->getLevel());
        if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
            if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                    $plot = $mp->getPlotByPosition($block);
                    if (($region = $wg->getRegionFromPosition($position)) !== ""){
                        if ($region->getFlag("pluginbypass") === "false"){
                            if ($region->getFlag("block-break") === "false"){
                                if($player->hasPermission("worldguard.break." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-break." . $region->getName())){
                                    $this->addProgress($event);
                                } elseif($player->hasPermission("worldguard.build-bypass")){
                                    $this->addProgress($event);
                                } else {
                                    $player->sendMessage("§cThis Lands Protect WorldGuard");
                                    $event->setCancelled();
                                }
                            } else {
                                $this->addProgress($event);
                            }
                        }
                    } elseif($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    } elseif($info === -1){
                        $this->addProgress($event);
                    } elseif($info !== true){
                        if($info['owner'] == $player->getName()){
                            $this->addProgress($event);
                        } elseif($player->isOp()){
                            $this->addProgress($event);
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect EconomoyLand");
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                        if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                            $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                            $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                            if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                if ($region->getFlag("pluginbypass") === "false"){
                                    if ($region->getFlag("block-break") === "false"){
                                        if($player->hasPermission("worldguard.break." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-break." . $region->getName())){
                                            $this->addProgress($event);
                                        } elseif($player->hasPermission("worldguard.build-bypass")){
                                            $this->addProgress($event);
                                        } else {
                                            $player->sendMessage("§cThis Lands Protect WorldGuard");
                                            $event->setCancelled();
                                        }
                                    } else {
                                        $this->addProgress($event);
                                    }
                                }
                            } elseif($info === -1){
                                $this->addProgress($event);
                            } elseif($info !== true){
                                if($info['owner'] == $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect EconomoyLand");
                                }
                            }
                        } else {
                            if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                                $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                                if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                    if ($region->getFlag("pluginbypass") === "false"){
                                        if ($region->getFlag("block-break") === "false"){
                                            if($player->hasPermission("worldguard.break." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-break." . $region->getName())){
                                                $this->addProgress($event);
                                            } elseif($player->hasPermission("worldguard.build-bypass")){
                                                $this->addProgress($event);
                                            } else {
                                                $player->sendMessage("§cThis Lands Protect WorldGuard");
                                                $event->setCancelled();
                                            }
                                        } else {
                                            $this->addProgress($event);
                                        }
                                    }
                                }
                            } else {
                                $this->addProgress($event);
                            }
                        }
                    } else {
                        if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                            $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                            if($info === -1){
                                $this->addProgress($event);
                            } elseif($info !== true){
                                if($info['owner'] == $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect EconomoyLand");
                                }
                            }
                        } else {
                            $this->addProgress($event);
                        }
                    }
                }
            } else {
                if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                    if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                        $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                        $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                        $plot = $mp->getPlotByPosition($block);
                        if (($region = $wg->getRegionFromPosition($position)) !== ""){
                            if ($region->getFlag("pluginbypass") === "false"){
                                if ($region->getFlag("block-break") === "false"){
                                    if($player->hasPermission("worldguard.break." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-break." . $region->getName())){
                                        $this->addProgress($event);
                                    } elseif($player->hasPermission("worldguard.build-bypass")){
                                        $this->addProgress($event);
                                    } else {
                                        $player->sendMessage("§cThis Lands Protect WorldGuard");
                                        $event->setCancelled();
                                    }
                                } else {
                                    $this->addProgress($event);
                                }
                            }
                        } elseif($plot !== null){
                            if($plot->owner != ""){
                                if($plot->owner === $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect MyPlot");
                                }
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        }
                    } else {
                        if(Server::getInstance()->getPluginManager()->getPlugin("WorldGuard")){
                            $wg = Server::getInstance()->getPluginManager()->getPlugin("WorldGuard");
                            if (($region = $wg->getRegionFromPosition($position)) !== ""){
                                if ($region->getFlag("pluginbypass") === "false"){
                                    if ($region->getFlag("block-break") === "false"){
                                        if($player->hasPermission("worldguard.break." . $region->getName()) || $event->getPlayer()->hasPermission("worldguard.block-break." . $region->getName())){
                                            $this->addProgress($event);
                                        } elseif($player->hasPermission("worldguard.build-bypass")){
                                            $this->addProgress($event);
                                        } else {
                                            $player->sendMessage("§cThis Lands Protect WorldGuard");
                                            $event->setCancelled();
                                        }
                                    } else {
                                        $this->addProgress($event);
                                    }
                                }
                            }
                        } else {
                            $this->addProgress($event);
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                        $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                        $plot = $mp->getPlotByPosition($block);
                        if($plot !== null){
                            if($plot->owner != ""){
                                if($plot->owner === $player->getName()){
                                    $this->addProgress($event);
                                } elseif($player->isOp()){
                                    $this->addProgress($event);
                                } else {
                                    $event->setCancelled();
                                    $player->sendMessage("§cThis Land Protect MyPlot");
                                }
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        }
                    } else {
                        $this->addProgress($event);
                    }
                }
            }
        } else {
            if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                    $plot = $mp->getPlotByPosition($block);
                    if($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    } elseif($info === -1){
                        $this->addProgress($event);
                    } elseif($info !== true){
                        if($info['owner'] == $player->getName()){
                            $this->addProgress($event);
                        } elseif($player->isOp()){
                            $this->addProgress($event);
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect EconomoyLand");
                        }
                    }
                } else {
                    if(Server::getInstance()->getPluginManager()->getPlugin("EconomyLand")){
                        $info = EconomyLand::getInstance()->callDB()->canTouch($block->x, $block->z, $block->getLevel()->getFolderName(), $player);
                        if($info === -1){
                            $this->addProgress($event);
                        } elseif($info !== true){
                            if($info['owner'] == $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect EconomoyLand");
                            }
                        }
                    } else {
                        $this->addProgress($event);
                    }
                }
            } else {
                if(Server::getInstance()->getPluginManager()->getPlugin("MyPlot")){
                    $mp = Server::getInstance()->getPluginManager()->getPlugin("MyPlot");
                    $plot = $mp->getPlotByPosition($block);
                    if($plot !== null){
                        if($plot->owner != ""){
                            if($plot->owner === $player->getName()){
                                $this->addProgress($event);
                            } elseif($player->isOp()){
                                $this->addProgress($event);
                            } else {
                                $event->setCancelled();
                                $player->sendMessage("§cThis Land Protect MyPlot");
                            }
                        } else {
                            $event->setCancelled();
                            $player->sendMessage("§cThis Land Protect MyPlot");
                        }
                    }
                } else {
                    $this->addProgress($event);
                }
            }
        }
    }

    /**
	 * @param BlockPlaceEvent|BlockBreakEvent $event
	 */
    public function addProgress($event){
        if(!$event->getBlock()->isValid()) return;
        $block = $event->getBlock();
        $player = $event->getPlayer();
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