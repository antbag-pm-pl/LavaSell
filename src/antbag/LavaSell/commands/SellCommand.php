<?php

namespace antbag\LavaSell\commands;

use antbag\LavaSell\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SellCommand extends Command
{

    public function __construct()
    {
        parent::__construct("sell", "Sell items", "/sell <hand|inv|reload>", ["s"]);
        $this->setPermission("lavasell.command.sell");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (empty($args) || count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /sell <hand|inv>");
            return false;
        }

        $subCommand = strtolower($args[0]);

        if ($sender instanceof Player) {
            switch ($subCommand) {
                case "hand":
                    Main::getInstance()->sellHand($sender);
                    break;
                case "inv":
                  if(!$this->testPermission($sender, "lavasell.command.inventory")) {
                    $sender->sendMessage(TextFormat::RED . 'You do not have permission to run this command');
                    return \false;
                }
                    Main::getInstance()->sellInventory($sender);
                    break;
                case "reload":
                    if(!$this->testPermission($sender, "lavasell.command.reload")) {
                      $sender->sendMessage(TextFormat::RED . 'You do not have permission to run this command');
                        return \false;
                    }
                    Main::getInstance()->loadPrices();
                    $sender->sendMessage(TextFormat::GREEN . "Sell prices reloaded.");
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Unknown subcommand. Use /sell <hand|inventory|reload>");
                    break;
            }
        } else {
            if ($subCommand === "reload") {
                Main::getInstance()->loadPrices();
                $sender->sendMessage(TextFormat::GREEN . "Sell prices reloaded.");
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            }
        }

        return true;
    }
}