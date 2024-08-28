<?php

namespace antbag\LavaSell;

use antbag\LavaSell\commands\SellCommand;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

class LavaSell extends PluginBase {


  private static $instance;
  public const PREFIX = '§8[§c§lLavaSell§r§8]§r';
  private array $prices;
  private Config $messages;

  public function onLoad() : void {
    self::$instance = $this;
  }

  public static function getInstance(): LavaSell {
    return self::$instance;
  }

  protected function onEnable(): void {
    if($this->getServer()->getPluginManager()->getPlugin('BedrockEconomy') === \null) {
      $this->getServer()->getPluginManager()->disablePlugin($this);
      $this->getLogger()->info('You need Bedrock Economy');
    }
    $this->loadPrices();
    $this->messages = new Config($this->getDataFolder() . 'messages.yml', Config::YAML);
  }

  public function loadPrices(): void
    {
        $config = new Config($this->getDataFolder() . "prices.yml", Config::YAML);
        $this->prices = $config->getAll();
    }

    public function savePrices(): void
    {
        $config = new Config($this->getDataFolder() . "prices.yml", Config::YAML);
        $config->setAll($this->prices);
        $config->save();
    }

    public function getPrice(Item $item): ?int
    {
        $itemName = StringToItemParser::getInstance()->lookupAliases($item)[0] ?? $item->getName();
        return $this->prices[$itemName] ?? null;
    }

    public function sellHand(Player $player): void
    {
        $item = $player->getInventory()->getItemInHand();
        $price = $this->getPrice($item);
        if ($price !== null) {
            $count = $item->getCount();
            $total = $price * $count;
            $player->getInventory()->removeItem($item);
            BedrockEconomyAPI::legacy()->addToPlayerBalance($player->getName(), $total);
            $player->sendMessage(self::PREFIX . " §7Sold §c" . $count . " §7" . $item->getName() . " for §c$" . $total);
        } else {
            $player->sendMessage("§cThis item cannot be sold.");
        }
    }

    public function sellInventory(Player $player): void
    {
        $inventory = $player->getInventory();
        $total = 0;
        $soldItems = [];

        foreach ($inventory->getContents() as $item) {
            $price = $this->getPrice($item);
            if ($price !== null) {
                $count = $item->getCount();
                $total += $price * $count;
                $inventory->removeItem($item);

                $itemName = $item->getName();
                if (!isset($soldItems[$itemName])) {
                    $soldItems[$itemName] = ["count" => 0, "total" => 0];
                }
                $soldItems[$itemName]["count"] += $count;
                $soldItems[$itemName]["total"] += $price * $count;
            }
        }

        if ($total > 0) {
            BedrockEconomyAPI::legacy()->addToPlayerBalance($player->getName(), $total);
            $summary = self::PREFIX . " §7Sold inventory items for §c$" . $total . ":\n";
            foreach ($soldItems as $name => $details) {
                $summary .= "§7- §c" . $details["count"] . " §7" . $name . " for §c$" . $details["total"] . "\n";
            }
            $player->sendMessage($summary);
        } else {
            $player->sendMessage("§cNo sellable items in inventory.");
        }
    }
}