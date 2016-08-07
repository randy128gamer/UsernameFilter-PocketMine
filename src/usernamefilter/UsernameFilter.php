<?php

namespace usernamefilter;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;

class UsernameFilter extends PluginBase implements Listener {
    
    /** @var WordList */
    private $wordList;
    
    private function initializeFiles() {
        @mkdir($this->getDataFolder());
        $this->wordList = new WordList($this->getDataFolder() . "words.txt");
    }
    
    public function onLoad() {
        $this->getLogger()->info("UsernameFilter is now loading...");
    }
    
    public function onEnable() {
        $this->getLogger()->info("UsernameFilter is now enabled.");
        $this->initializeFiles();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onDisable() {
        $this->getLogger()->info("UsernameFilter is now disabled.");
    }
    
    private function checkPlayerUsernames() {
        foreach ($this->getServer()->getOnlinePlayers() as $players) {
            foreach ($this->wordList->forEachWord() as $words) {
                if (strpos(strtolower($players->getName()), strtolower($words)) >= 0
                        || strpos(strtolower($players->getDisplayName()), strtolower($words))) {
                    $players->kick("§bYou're not allowed to use that username in this serer.");
                }
            }
        }
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command->getName()) {
            case "ufadd":
                if ($sender->hasPermission("usernamefilter.command.ufadd")) {
                    if (count($args) <= 0) {
                        $sender->sendMessage("§3Usage: §b/udadd <word>");
                        return false;
                    }
                    if ($this->wordList->contains($args[0])) {
                        $sender->sendMessage("§6Word already exists.");
                        return false;
                    }
                    $this->wordList->add($args[0]);
                    $sender->sendMessage("§aWord \"" . $args[0] . "\" has been added successfully to the list.");
                    $this->checkPlayerUsernames();
                } else {
                    $sender->sendMessage("§4You don't have enough access to do this.");
                }
                return true;
            case "ufremove":
                if ($sender->hasPermission("usernamefilter.command.ufremove")) {
                    if (count($args) <= 0) {
                        $sender->sendMessage("§3Usage: §b/ufremove <word>");
                        return false;
                    }
                    if (!$this->wordList->contains($args[0])) {
                        $sender->sendMessage("§6Word not exists.");
                        return false;
                    }
                    $this->wordList->remove($args[0]);
                    $sender->sendMessage("§aWord \"" . $args[0] . "\" has been removed successfully to the list.");
                } else {
                    $sender->sendMessage("§4You don't have enough access to do this.");
                }
                return true;
            case "ufreload":
                if ($sender->hasPermission("usernamefilter.command.ufreload")) {
                    $sender->sendMessage("§aConfiguration reloaded.");
                    $this->wordList->reload();
                } else {
                    $sender->sendMessage("§4You don't have enough access to do this.");
                }
                return true;
        }
    }


    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        foreach ($this->wordList->forEachWord() as $words) {
            if (strpos(strtolower($player->getName()), strtolower($words)) >= 0
                    || strpos(strtolower($player->getDisplayName()), strtolower($words)) >= 0) {
                $event->setJoinMessage("");
                $player->kick("§bYou're not allowed to use that username in this serer.", false);
                break;
            }
        }
    }
}