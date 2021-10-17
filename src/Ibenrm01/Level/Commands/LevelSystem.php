<?php

namespace Ibenrm01\Level\Commands;

use pocketmine\command\{
    Command,
    PluginCommand,
    CommandSender
};
use pocketmine\Player;
use Ibenrm01\Level\Main;
use libs\jojoe77777\FormAPI\{
    SimpleForm, CustomForm
};

class LevelSystem extends PluginCommand {

    const MSG = "§l§eLEVEL SYSTEM §7// §r";

    public function __construct(Main $plugin){
        parent::__construct('levelsystem', $plugin);
        $this->setAliases(['levels', 'level']);
        $this->setDescription('LevelSystem Command');
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     */
    public function topUI(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null) {
            if($data === null or $data === 0){
                $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
                return;
            }
        });
        $swallet = $this->plugin->top->getAll();
        $c = count($swallet);
        arsort($swallet);
        $i = 1;
        $top = "§7       ===== §l§eTOP LEVEL §r§7========";
        $pesan = "";
        foreach($swallet as $name => $amount) :
            $pesan .= $this->plugin->replace($this->plugin->getConfig()->get("content-top"), [
                "top" => $i,
                "username" => $name,
                "level" => $amount
            ])."\n     ";
            if($i > 9){
                break;
            }
            ++$i;
        endforeach;
            $form->setTitle($this->plugin->getConfig()->get('title-top'));
            $form->setContent($top."\n     ".$pesan."\n       §7==========================");
            $form->addButton("§cEXIT");
            $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param string $label
     */
    public function checkStats(Player $player, string $label){
        $target = $this->plugin->getServer()->getPlayer($label);
        if($target instanceof Player){
            $status = "§aONLINE";
            $level = $this->plugin->onTags($target)."".$this->plugin->checkLevel($player, $target->getName(), "UI");
            $top = $this->plugin->checkTop($player, $target->getName(), "UI");
            $progress_int = $this->plugin->checkProgress($player, $target->getName(), "UI");
            if($this->plugin->data[$target->getName()]['progress'] < $this->plugin->data[$target->getName()]['needprogress']){
                $total = $this->plugin->data[$target->getName()]['progress'] / $this->plugin->data[$target->getName()]['needprogress'] * 100;
                $current = $total / 10.5;
                $db = "§a".str_repeat("■", round($current, 0))."§c".str_repeat("■", round(10 - $current, 0));
            } else {
                $db = "§a■■■■■■■■■■";
            }
            $need_progress = $this->plugin->checkNProgress($player, $target->getName(), "UI");
        } else {
            $status = "§cOFFLINE";
            $level = "§cNULL";
            $top = "§cNULL";
            $progress_int = "§cNULL";
            $db = "§cNULL";
            $need_progress = "§cNULL";
        }
        $form = new SimpleForm(function(Player $player, int $data = null) use ($label, $target, $progress_int, $need_progress){
            if($data === null or $data === 1){
                $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
                return;
            }
            if($target instanceof Player){
                if($target->getName() == $player->getName()){
                    if($progress_int > $need_progress){
                        $this->plugin->upgrade($target, "MSG");
                    } else {
                        $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
                    }
                } else {
                    $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
                }
            } else {
                $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
            }
        });
        $form->setTitle($this->plugin->getConfig()->get("title-stats"));
        $form->setContent($this->plugin->replace($this->plugin->getConfig()->get("content-stats"), [
            "username" => $label,
            "status" => $status,
            "level" => $level,
            "progress_int" => $progress_int,
            "progress_structur" => $db,
            "need_progress_int" => $need_progress,
            "top-level" => $top
        ]));
        if($target instanceof Player){
            if($target->getName() == $player->getName()){
                if($progress_int > $need_progress){
                    $form->addButton("§aUPGRADE\n§fYou have levelup");
                } else {
                    $prs = $need_progress - $progress_int;
                    $form->addButton("§cUPGRADE\n§fYou need $prs progress");
                }
            } else {
                $form->addButton("§cTHIS NOT YOUR STATS");
            }
        } else {
            $form->addButton("§c~~~");
        }
        $form->addButton("§cEXIT");
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     */
    public function settingsUI(Player $player){
        $form = new CustomForm(function(Player $player, array $data = null){
            if($data === null){
                $player->sendMessage(self::MSG.$this->plugin->lang->get("exit-form"));
                return;
            }
            if($data[0] === true){
                $this->plugin->data[$player->getName()]['settings']['notice-levelup'] = "ON";
            } else {
                $this->plugin->data[$player->getName()]['settings']['notice-levelup'] = "OFF";
            }
            if($data[1] === true){
                $this->plugin->data[$player->getName()]['settings']['notice-progress'] = "ON";
            } else {
                $this->plugin->data[$player->getName()]['settings']['notice-progress'] = "OFF";
            }
            if($data[2] === true){
                if($player->hasPermission("settings.fireworks")){
                    $this->plugin->data[$player->getName()]['settings']['firework'] = "ON";
                } else {
                    $player->sendMessage(self::MSG.$this->plugin->lang->get("not.permissions"));
                }
            } else {
                $this->plugin->data[$player->getName()]['settings']['firework'] = "OFF";
            }
        });
        $form->setTitle($this->plugin->getConfig()->get("title-settings"));
        if($this->plugin->data[$player->getName()]['settings']['notice-levelup'] == "ON"){
            $form->addToggle("§bNOTICE LEVEL UP", true);
        } elseif($this->plugin->data[$player->getName()]['settings']['notice-levelup'] == "OFF"){
            $form->addToggle("§bNOTICE LEVEL UP", false);
        }
        if($this->plugin->data[$player->getName()]['settings']['notice-progress'] == "ON"){
            $form->addToggle("§bNOTICE GET PROGRESS", true);
        } elseif($this->plugin->data[$player->getName()]['settings']['notice-progress'] == "OFF"){
            $form->addToggle("§bNOTICE GET PROGRESS", false);
        }
        if($this->plugin->data[$player->getName()]['settings']['firework'] == "ON"){
            $form->addToggle("§bLEVEL UP FIREWORK", true);
        } elseif($this->plugin->data[$player->getName()]['settings']['firework'] == "OFF"){
            $form->addToggle("§bLEVEL UP FIREWORK", false);
        }
        $form->sendToPlayer($player);
    }

    /**
     * @param CommandSender $sender
     * @param string $label
     * @param array $args
     * 
     * @return bool
     */
    public function execute(CommandSender $sender, string $label, array $args): bool{
        if(!$sender instanceof Player){
            $sender->sendMessage(self::MSG.$this->plugin->lang->get("use.command.in.game"));
            return true;
        }
        if(!isset($args[0])){
            $sender->sendMessage("§7===== §l§eLEVELSYSTEM COMMANDS §r§7=====\n§f- /levels help §7|| §bList all commands\n§f- /levels addprogress {player-name} {amount} §7|| §bGive a progress to player-name\n§f- /levels setprogress (player-name) (amount) §7|| §bSet a progress player-name\n§f- /levels addlevels (player-name) (amount) §7|| §badd levels player-name\n§f- /setlevel (player-name) (amount) §7|| §bset levels player-name\n§f- /levels check [type:level/progress/top] (player-name) §7|| §bCheck a level/progress/top player-name\n§f- /levels stats §7|| §bcheck your stats\n§f- /levels upgrade §7|| §bupgrade your levels\n§f- /levels settings §7|| §bsettings your system");
            return true;
        }
        switch($args[0]){
            case "stats":
                if(!isset($args[1])){
                    $this->checkStats($sender, $sender->getName());
                    return true;
                }
                $this->checkStats($sender, $args[1]);
                return true;
            break;
            case "upgrade":
                $this->plugin->upgrade($sender, "MSG");
                return true;
            break;
            case "addprogress":
                if(!$sender->hasPermission("progress.cmd")) return true;
                if(isset($args[1]) && isset($args[2])){
                    if(!is_numeric($args[2])){
                        $sender->sendMessage(self::MSG."§b/levelsystem addprogress (player-name) (number)");
                        return true;
                    }
                    $this->plugin->addProgress($sender, $args[1], $args[2], "MSG");
                    return true;
                } else {
                    $sender->sendMessage(self::MSG."§b/levelsystem addprogress (player-name) (number)");
                    return true;
                }
            break;
            case "setprogress":
                if(!$sender->hasPermission("progress.cmd")) return true;
                if(isset($args[1]) && isset($args[2])){
                    if(!is_numeric($args[2])){
                        $sender->sendMessage(self::MSG."§b/levelsystem setprogress (player-name) (number)");
                        return true;
                    }
                    $this->plugin->setProgress($sender, $args[1], $args[2], "MSG");
                    return true;
                } else {
                    $sender->sendMessage(self::MSG."§b/levelsystem setprogress (player-name) (number)");
                    return true;
                }
            break;
            case "setlevel":
                if(!$sender->hasPermission("level.cmd")) return true;
                if(isset($args[1]) && isset($args[2])){
                    if(!is_numeric($args[2])){
                        $sender->sendMessage(self::MSG."§b/levelsystem addprogress (player-name) (number)");
                        return true;
                    }
                    $this->plugin->setLevel($sender, $args[1], $args[2], "MSG");
                    return true;
                } else {
                    $sender->sendMessage(self::MSG."§b/levelsystem setlevel (player-name) (number)");
                    return true;
                }
            break;
            case "check":
                if(!isset($args[1])){
                    $sender->sendMessage(self::MSG."§b/levelsystem check (type[level/progress/top]) (player-name)");
                    return true;
                }
                switch($args[1]){
                    case "level":
                        if(!isset($args[2])){
                            $sender->sendMessage(self::MSG."§b/levelsystem check (type[level/progress/top]) (player-name)");
                            return true;
                        }
                        $this->plugin->checkLevel($sender, $args[2], "MSG");
                        return true;
                    break;
                    case "progress":
                        if(!isset($args[2])){
                            $sender->sendMessage(self::MSG."§b/levelsystem check (type[level/progress/top]) (player-name)");
                            return true;
                        }
                        $this->plugin->checkProgress($sender, $args[2], "MSG");
                        return true;
                    break;
                    case "top":
                        if(!isset($args[2])){
                            $sender->sendMessage(self::MSG."§b/levelsystem check (type[level/progress/top]) (player-name)");
                            return true;
                        }
                        $this->plugin->checkTop($sender, $args[2], "MSG");
                        return true;
                    break;
                }
                return true;
            break;
            case "top":
                $this->topUI($sender);
                return true;
            break;
            case "settings":
                $this->settingsUI($sender);
                return true;
            break;
            case "help":
                $sender->sendMessage("§7===== §l§eLEVELSYSTEM COMMANDS §r§7=====\n§f- /levels help §7|| §bList all commands\n§f- /levels addprogress {player-name} {amount} §7|| §bGive a progress to player-name\n§f- /levels setprogress (player-name) (amount) §7|| §bSet a progress player-name\n§f- /levels addlevels (player-name) (amount) §7|| §badd levels player-name\n§f- /setlevel (player-name) (amount) §7|| §bset levels player-name\n§f- /levels check [type:level/progress/top] (player-name) §7|| §bCheck a level/progress/top player-name\n§f- /levels stats §7|| §bcheck your stats\n§f- /levels upgrade §7|| §bupgrade your levels\n§f- /levels settings §7|| §bsettings your system");
                return true;
            break;
        }
        return true;
    }
}