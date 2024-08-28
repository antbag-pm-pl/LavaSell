<?php

namespace antbag\LavaSell\commands;

use antbag\LavaSell\LavaSell;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class SellCommand extends Command implements PluginOwned
{

    public function __construct()
    {
        parent::__construct("sell", "Sell items", "/sell <hand|inv|reload>", ["s"]);
        $this->setPermission("lavasell.command.sell");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {

        if(!$sender instanceof Player) {
            return;
        }

        if (!$this->testPermission($sender)) {
            return;
        }

        if (empty($args) || count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /sell <hand|inv>");
            return;
        }

        $subCommand = strtolower($args[0]);

        if ($sender instanceof Player) {
            switch ($subCommand) {
                case "hand":
                    LavaSell::getInstance()->sellHand($sender);
                    break;
                case "inv":
                  if(!$this->testPermission($sender, "lavasell.command.inventory")) {
                    $sender->sendMessage(TextFormat::RED . 'You do not have permission to run this command');
                    return;
                }
                    LavaSell::getInstance()->sellInventory($sender);
                    break;
                case "reload":
                    if(!$this->testPermission($sender, "lavasell.command.reload")) {
                      $sender->sendMessage(TextFormat::RED . 'You do not have permission to run this command');
                        return;
                    }
                    LavaSell::getInstance()->loadPrices();
                    $sender->sendMessage(TextFormat::GREEN . "Sell prices reloaded.");
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Unknown subcommand. Use /sell <hand|inventory|reload>");
                    break;
            }
        } else {
            if ($subCommand === "reload") {
                LavaSell::getInstance()->loadPrices();
                $sender->sendMessage(TextFormat::GREEN . "Sell prices reloaded.");
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            }
        }
    }

    public function getOwningPlugin(): Plugin
    {
        return LavaSell::getInstance();
    }
}