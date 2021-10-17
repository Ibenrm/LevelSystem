<?php

namespace Ibenrm01\Level;

use pocketmine\{
    Server, Player
};
use pocketmine\plugin\{
    Plugin, PluginBase
};
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\level\{
    Level, Position
};
use pocketmine\entity\Entity;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;

use BlockHorizons\Fireworks\item\Fireworks;
use BlockHorizons\Fireworks\entity\FireworksRocket;

use pocketmine\math\Vector3;
use pocketmine\math\Vector2;

class Main extends PluginBase implements Listener {
    
    public $data;

    const MSG = "§l§eLEVEL SYSTEM §7// §r";

    public function onEnable(){
        @mkdir($this->getDataFolder()."/lang/");
        $this->saveDefaultConfig();
        if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null){
            $this->getLogger()->info("YOU NEED PLUGIN EconomyAPI");
            $this->getServer()->getPluginManager()->disablePlugin("EconomyAPI");
        } else {
            $this->getServer()->getCommandMap()->register("levelsystem", new Commands\LevelSystem($this));
            $this->getServer()->getPluginManager()->registerEvents(new Event\EventListener($this), $this);
        }
        if(!file_exists($this->getDataFolder()."database.yml")){
            $this->saveResource("lang/Lang.yml");
            new Config($this->getDataFolder()."database.yml", Config::YAML);
            $this->getLogger()->info("§aSuccessfully set new database");
            $this->lang = new Config($this->getDataFolder()."/lang/Lang.yml", Config::YAML);
            $this->data = yaml_parse(file_get_contents($this->getDataFolder()."database.yml"));
            $this->top = new Config($this->getDataFolder()."toplevel.yml", Config::YAML);
            sleep(1);
        } else {
            $this->lang = new Config($this->getDataFolder()."/lang/Lang.yml", Config::YAML);
            $this->data = yaml_parse(file_get_contents($this->getDataFolder()."database.yml"));
            $this->top = new Config($this->getDataFolder()."toplevel.yml", Config::YAML);
        }
        ItemFactory::registerItem(new Fireworks());
        Item::initCreativeItems();
        if(!Entity::registerEntity(FireworksRocket::class, false, ["FireworksRocket"])){
            $this->getLogger()->error("Failed Register FireworksRocket Entity with savename 'FireworksRocket'");
        }
    }

    public function onDisable(){
        file_put_contents($this->getDataFolder()."database.yml", yaml_emit($this->data));
    }

    /**
     * @return string
     */
    public function getFireworksColor(): string {
        $colors = [
            Fireworks::COLOR_BLACK,
            Fireworks::COLOR_RED,
            Fireworks::COLOR_BROWN,
            Fireworks::COLOR_BLUE,
            Fireworks::COLOR_GRAY,
            Fireworks::COLOR_GOLD,
            Fireworks::COLOR_WHITE,
            Fireworks::COLOR_PINK,
            Fireworks::COLOR_GREEN,
            Fireworks::COLOR_YELLOW
        ];

        return $colors[array_rand($colors)];
    }

    /**
     * @param Player $player
     */
    public function onFireworks(Player $player){
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $fw = ItemFactory::get(Item::FIREWORKS);
        if($fw instanceof Fireworks){
            $fw->addExplosion(mt_rand(3, 4), $this->getFireworksColor(), "", true, false);
            $fw->addExplosion(mt_rand(4, 4), $this->getFireworksColor(), "", true, false);
            $fw->addExplosion(mt_rand(0, 2), $this->getFireworksColor(), "", true, false);
            $fw->setFlightDuration(1);
            $levelpl = $player->getLevel();
            $vector3 = new Position($x, $y + 2, $z, $levelpl);
            $nbt = FireworksRocket::createBaseNBT($vector3, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
            $entity = FireworksRocket::createEntity("FireworksRocket", $levelpl, $nbt, $fw);
            if($entity instanceof FireworksRocket){
                $entity->spawnTo($player);
            }
        }
    }

    /**
     * @param string $message
     * @param array $keys
     * 
     * @return string
     */
    public function replace(string $message, array $keys): string{
        foreach($keys as $word => $value){
            $message = str_replace("{".strtolower($word)."}", $value, $message);
        }
        return $message;
    }

    /**
     * @param Player $player
     */
    public function onTags(Player $player){
        $test = [];
        if($this->getConfig()->get("max-level") === true){
            if($this->getConfig()->get("color-level") === true){
                foreach($this->getConfig()->getAll()['color-list'] as $list) :
                    $values = explode(":", $list);
                    $check = $this->checkLevel($player, $player->getName(), "INFO");
                    if($check >= $values[0]){
                        $test[$player->getName()] = $values[1];
                        if($values[0] >= $this->getConfig()->get("max-level-int")){
                            if($check >= $values[0]){
                                break;
                            }
                        }
                    }
                endforeach;
                if(isset($test[$player->getName()])){
                    return $test[$player->getName()];
                } else {
                    return "§f";
                }
            } else {
                return "§f";
            }
        } else {
            if($this->getConfig()->get("color-level") === true){
                foreach($this->getConfig()->getAll()['color-list'] as $list) :
                    $values = explode(":", $list);
                    $check = $this->checkLevel($player, $player->getName(), "return");
                    if($check >= $values[0]){
                        $test[$player->getName()] = $values[1];
                        if($values[0] > $check){
                            break;
                        }
                    }
                endforeach;
                if(isset($test[$player->getName()])){
                    return $test[$player->getName()];
                } else {
                    return "§f";
                }
            } else {
                return "§f";
            }
        }
    }

    /**
     * @param Player $player
     * @param string $target
     * @param string $type
     */
    public function checkLevel(Player $player, string $label, string $type){
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        if($type == "MSG"){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("check.level.player"), [
                "username" => $target->getName(),
                "level" => $this->data[$target->getName()]["level"]
            ]));
            return;
        }
        if($this->getConfig()->get("max-level") === true){
            if($this->data[$target->getName()]['level'] >= $this->getConfig()->get("max-level-int")){
                return "MAX";
            }
            return $this->data[$target->getName()]["level"];
        } else {
            return $this->data[$target->getName()]["level"];
        }
    }

    /**
     * @param Player $player
     * @param string $target
     * @param string $type
     */
    public function checkProgress(Player $player, string $label, string $type){
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        if($type == "MSG"){
            if($this->data[$target->getName()]['progress'] < $this->data[$target->getName()]['needprogress']){
                $total = $this->data[$target->getName()]['progress'] / $this->data[$target->getName()]['needprogress'] * 100;
                $current = $total / 10.5;
                $db = "§a".str_repeat("■", round($current, 0))."§c".str_repeat("■", round(10 - $current, 0));
            } else {
                $db = "§a■■■■■■■■■■";
            }
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("check.progress.player"), [
                "username" => $target->getName(),
                "progress_int" => $this->data[$target->getName()]["progress"],
                "progress_structur" => $db,
                "need_progress_int" => $this->data[$target->getName()]['needprogress']
            ]));
            return;
        }
        return $this->data[$target->getName()]['progress'];
    }

    /**
     * @param Player $player
     * @param string $target
     * @param string $type
     */
    public function checkNProgress(Player $player, string $label, string $type){
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        if($type == "MSG"){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("check.need.progress.player"), [
                "username" => $target->getName(),
                "need_progress_int" => $this->data[$target->getName()]['needprogress']
            ]));
            return;
        }
        return $this->data[$target->getName()]['needprogress'];
    }

    /**
     * @param Player $player
     * @param string $label
     * @param $int
     * @param string $type
     */
    public function addProgress(Player $player, string $label, $int, string $type){
        if($int < 0 or $int > $this->getConfig()->get("max-numeric")){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("add.progress.maximum"), [
                "maximum" => $this->getConfig()->get("max-numeric")
            ]));
            return;
        }
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        $this->data[$target->getName()]['progress'] += $int;
        if($target->getName() == $player->getName()){
            if($type == "MSG"){
                $player->sendMessage(self::MSG.$this->replace($this->lang->get("add.progress.yourself"), [
                    "progress" => $int
                ]));
                return;
            }
            return;
        }
        $player->sendMessage(self::MSG.$this->replace($this->lang->get("add.progress.player"), [
            "username" => $target->getName(),
            "progress" => $int
        ]));
        $target->sendMessage(self::MSG.$this->replace($this->lang->get("get.add.progress"), [
            "username" => $player->getName(),
            "progress" => $int
        ]));
    }

    /**
     * @param Player $player
     * @param string $label
     * @param $int
     */
    public function setProgress(Player $player, string $label, $int){
        if($int < 0 or $int > $this->getConfig()->get("max-numeric")){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.progress.maximum"), [
                "maximum" => $this->getConfig()->get("max-numeric")
            ]));
            return;
        }
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        $this->data[$target->getName()]['progress'] = $int;
        if($target->getName() == $player->getName()){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.progress.yourself"), [
                "progress" => $int
            ]));
            return;
        }
        $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.progress.player"), [
            "username" => $target->getName(),
            "progress" => $int
        ]));
        $target->sendMessage(self::MSG.$this->replace($this->lang->get("get.set.progress"), [
            "username" => $player->getName(),
            "progress" => $int
        ]));
    }

    /**
     * @param Player $player
     * @param string $label
     * @param $int
     * @param string $type
     */
    public function setLevel(Player $player, string $label, $int, string $type){
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        if($this->getConfig()->get("max-level") === true){
            if($int <= $this->getConfig()->get("max-level-int")){
                $this->data[$target->getName()]['level'] = $int;
                $this->top->set($target->getName(), $int);
                $this->top->save();
            } else {
                $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.level.maximum"), [
                    "max-level" => $this->getConfig()->get("max-level-int")
                ]));
                return;
            }
        } else {
            $this->data[$target->getName()]['level'] = $int;
            $this->data[$target->getName()]['progress'] = 0;
            $this->top->set($target->getName(), $int);
            $this->top->save();
        }
        if($target->getName() == $player->getName()){
            if($type == "MSG"){
                $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.level.yourself"), [
                    "level" => $int
                ]));
                return;
            }
            return;
        }
        $player->sendMessage(self::MSG.$this->replace($this->lang->get("set.level.player"), [
            "username" => $target->getName(),
            "level" => $int
        ]));
        $target->sendMessage(self::MSG.$this->replace($this->lang->get("get.set.level"), [
            "username" => $player->getName(),
            "level" => $int
        ]));
        $this->onTags($target);
    }

    /**
     * @param Player $player
     */
    public function register(Player $player){
        if(isset($this->data[$player->getName()])){
            return;
        }
        $this->data[$player->getName()]['level'] = 1;
        $this->data[$player->getName()]['progress'] = 0;
        $this->data[$player->getName()]['needprogress'] = 45;
        $this->data[$player->getName()]['settings']['notice-levelup'] = "ON";
        $this->data[$player->getName()]['settings']['firework'] = "OFF";
        $this->data[$player->getName()]['settings']['notice-progress'] = "ON";
        $this->top->set($player->getName(), 1);
        $this->top->save();
        return;
    }

    /**
     * @param Player $player
     * @param string $type
     */
    public function upgrade(Player $player, string $type){
        $exp = $this->checkProgress($player, $player->getName(), "INFO");
        $expn = $this->checkNProgress($player, $player->getName(), "INFO");
        if($exp >= $expn){
            $jumlah = $this->data[$player->getName()]['level'] + 1;
            if($this->data[$player->getName()]['settings']['notice-levelup'] == "ON"){
                $player->sendMessage(self::MSG.$this->replace($this->lang->get("notice.level.up"), [
                    "before" => $this->data[$player->getName()]['level'],
                    "after" => $jumlah
                ]));
            }
            if($this->data[$player->getName()]['settings']['firework'] == "ON"){
                $this->onFireworks($player);
            }
            $this->data[$player->getName()]['progress'] -= $expn;
            $this->setLevel($player, $player->getName(), $jumlah, "SET");
            $this->data[$player->getName()]['needprogress'] += $this->getConfig()->get('levelup-expneed');
        } else {
            if($type == "MSG"){
                $jumlah = $expn - $exp;
                $player->sendMessage(self::MSG.$this->replace($this->lang->get("you.need.progress"), [
                    "progress" => $jumlah
                ]));
                return;
            }
            return;
        }
    }

    /**
     * @param Player $player
     * @param string $label
     * @param string $type
     */
    public function checkTop(Player $player, string $label, string $type){
        $target = $this->getServer()->getPlayer($label);
        if(!$target instanceof Player){
            $player->sendMessage(self::MSG.$this->lang->get("player.not.found"));
            return;
        }
        $swallet = $this->top->getAll();
        $c = count($swallet);
        arsort($swallet);
        $i = 1;
        $top = [];
        foreach($swallet as $name => $amount) :
            if($name == $target->getName()){
                $top['top'] = "#".$i;
                $top['many'] = $amount;
                break;
            }
            ++$i;
        endforeach;
        if($type == "MSG"){
            $player->sendMessage(self::MSG.$this->replace($this->lang->get("check.top.player"), [
                "username" => $target->getName(),
                "top" => $top['top'],
                "amount" => $top['many']
            ]));
            return;
        }
        return $top['top'];
    }
}