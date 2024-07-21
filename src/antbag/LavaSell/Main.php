<?php

namespace antbag\LavaSell;

use antbag\LavaSell\commands\SellCommand;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

class Main extends PluginBase {

  public $sellManager;
  private static $instance;
  public const PREFIX = '§8[§c§lLavaSell§r§8]§r';
  private array $prices;

  public function onLoad() : void {
    self::$instance = $this;
  }

  public function onEnable(): void {
    if($this->getServer()->getPluginManager()->getPlugin('BedrockEconomy') === \null) {
      $this->getServer()->getPluginManager()->disablePlugin($this);
      $this->getLogger()->info('You need Bedrock Economy');
    }
    $this->sellManager = new Config($this->getDataFolder() . "prices.yml", Config::YAML);
    $this->getServer()->getCommandMap()->register('sell', new SellCommand());
  }

  public function loadPrices(): void {
    $this->prices = $this->sellManager->getAll();
  }

  public function savePrices(): void {
    $this->sellManager->setAll($this->prices);
    $this->sellManager->save();
  }

  public function getPrice(Item $item): ?int {
    $itemName = StringToItemParser::getInstance()->lookupAliases($item)[0] ?? $item->getName();
    return $this->prices[$itemName] ?? \null;
  }

  public function sellHand(Player $player): void
    {
        $item = $player->getInventory()->getItemInHand();
        $price = $this->getPrice($item);
        if ($price !== null) {
            $count = $item->getCount();
            $total = $price * $count;
            $player->getInventory()->removeItem($item);
            BedrockEconomyAPI::legacy()->addToPlayerBalance($player, $price);
            $player->sendMessage("§7Sold §c" . $count . " §7" . $item->getName() . " for §c$" . $total);
        } else {
            $player->sendMessage("This item cannot be sold.");
        }
    }

    public function sellInventory(Player $player): void
    {
        $inventory = $player->getInventory();
        $total = 0;
        foreach ($inventory->getContents() as $item) {
            $price = $this->getPrice($item);
            if ($price !== null) {
                $count = $item->getCount();
                $total += $price * $count;
                $inventory->removeItem($item);
            }
        }
        if ($total > 0) {
          BedrockEconomyAPI::legacy()->addToPlayerBalance($player, $price);
            $player->sendMessage("§7Sold inventory items for §c$" . $total);
        } else {
            $player->sendMessage("No sellable items in inventory.");
        }
    }

    public static function getInstance(): Main {
      return self::$instance;
    }

}