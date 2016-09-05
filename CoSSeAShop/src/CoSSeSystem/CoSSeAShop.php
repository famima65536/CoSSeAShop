<?php

namespace CoSSeSystem;

/*
Cosmo Sunrise Server's AdminShop System.
Development start date: 2016/08/24

このプラグインはpopke LISENCEを理解および同意した上で使用する事。
また、無駄なコードはことごとく排除するよう書く事を心がける事。
*/

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;
use pocketmine\inventory;

class CoSSeAShop extends PluginBase implements Listener {

	function onEnable() {
		$this->getLogger()->info(TF::GREEN."CoSSeAShop is Enabled!");
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		if($this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI") != null){
            $this->CMA = $this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI");
        }
	}

	function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$blockID = $block->getID();
		if ($blockID == "63" or $blockID == "68") {
			$tile = $event->getBlock()->getLevel()->getTile($block);
			if ($tile instanceof Sign) {
				$text = $tile->getText();
				if ($player->isOP()) {
					if ($text[0] == "§6§lADMINSHOP") {
						$player->sendMessage("[§aCoSSe§f]"."\n"."ADMINSHOPを壊しました。");
					}
				} else {
					$event->setCancelled();
					$player->sendMessage("[§aCoSSe§f]"."\n"."ADMINSHOPはOperator権限者しか破壊できません。");
				}
			}
		}
	}

	function onInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$user = $player->getName();
		$block = $event->getBlock();
		$blockID = $block->getID();
		if($blockID == "63" or $blockID == "68") {
			$tile = $event->getBlock()->getLevel()->getTile($block);
			if ($tile instanceof Sign) {
				$text = $tile->getText();
				if ($text[0]  == "§6§lADMINSHOP") {
					$CMA = $this->CMA->getMoney($user);
					$p3 = explode(": ",$text[3]);
					$price = $p3[1];
					if($price < $CMA) {
						$p1 = explode(": ", $text[1]);
						$p = explode(".", $p1[1]);
						$pid = $p[0];
						$pmeta = $p[1];
						$p2 = explode(": ", $text[2]);
						$amount = $p2[1];
						$iName = Block::get($pid)->getName();
						$item = Item::get($pid, $pmeta, $amount);
						if(($player->getInventory())->canAddItem($item)) {
							$player->getInventory()->addItem($item);
							$this->CMA->addMoney($user, - $price);
							$player->sendMessage("[§aCoSSe§f]"."\n"."{$iName}を{$amount}個購入しました。");
						}else{
							$player->sendMessage("[§aCoSSe§f]"."\n".TF::RED."インベントリがいっぱいで購入できません！");
						}
					}else{
						$player->sendMessage("[§aCoSSe§f]"."\n".TF::RED."所持金が足りないため購入できません！");
					}
				}
			}
		}
	}

	function onSignChange(SignChangeEvent $event) {
		$player = $event->getPlayer();
		$lines = $e->getLines();
		$key = $event->getLine(0);
			if($key == "ashop") {
				if($player->isOP()) {
					if(is_numeric($lines[2]) and is_numeric($lines[3])  and (preg_match('/^[0-9]+:[0-9]+$/', $lines[3]) or is_numeric($lines[3])) )
					{
						if(!is_numeric($lines[3]))
						{
							$itemd = explode(":",$lines[3]);
							$item = Item::get($itemd[0], $itemd[1], $lines[1]);
						}else{
							$item =Item::get($lines[3], 0, $lines[1]);
						}
						
				
						$price = $lines[2];
						$amount = $lines[1];
						$p = [$item->getID(), $item->getDamage()];
						$pid = $p[0];
						$pmeta = $p[1];
						$event->setLine(0, "§6§lADMINSHOP");
						$event->setLine(1, "{$iName} : {$pid}.{$pmeta}");
						$event->setLine(2, "取引量 : {$amount}");
						$event->setLine(3, "取引値 : {$price}");
						$player->sendMessage("[§aCoSSe§f]"."\n"."AdmiShopを作成しました！"."\n"."$item->getName() : $pid . $pmeta 取引量 : $amount 取引値 : $price");
				
						}else{
							$player->sendMessage("[§aCoSSe§f]"."\n".TF::RED."情報をきちんと入力してください");
						}
					}else{
					$player->sendMessage("[§aCoSSe§f]"."\n".TF::RED."OP以外はAdminShopを作成できません。");
				}
			}
	}
}
