<?php

namespace HaKunZz\Lumberjack;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\Entity;
use pocketmine\entity\Human;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat as T;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

class Lumberjack extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->getLogger()->info(T::GREEN . "Activated By HaKunZz Yeah :D");
		@mkdir($this->getDataFolder());
		$this->lumber = new Config($this->getDataFolder() . "lumberjack.yml", Config::YAML, array());
		$this->exp = new Config($this->getDataFolder() . "exp.yml", Config::YAML, array());
		$this->cost = new Config($this->getDataFolder() . "cost.yml", Config::YAML, array());
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onJoin(PlayerJoinEvent $e){
		$sender = $e->getPlayer();
		$this->Plvl($sender);
	}
	
	public function onBreak(BlockBreakEvent $e){
		$sender = $e->getPlayer();
		$tools = $e->getItem();
		$lvl = $this->getLevel($sender);
		$xp = $this->getExp($sender);
		$cost = $this->getCost($sender);
		$axe = array(271, 275, 258, 286, 279);
		if(in_array($tools->getId(), $axe)){
			$this->addExp($sender, 1);
			$sender->sendTip("§8-=-§6Lumberjack§8-=-\n§8-+Level : §6".$lvl."\n§8-+Uplift : §6".$xp."/".$cost);
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch($cmd->getName()){
			case "lujk":
				if($sender instanceof Player) {
					$this->Menu($sender);
					return true;
				}else{
					$sender->sendMessage("Please Run This Command In-Game!");
				}
		}
	}
	
	public function Menu($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $sender, int $data = null) {
			$result = $data;
			if($result === null){
				return true;
			}
	       switch($result){
				case 0:
				$this->TopLumber($sender);
				break;
				case 1:
				$sender->addTitle("§cSAYONARA", "BYE BYE");
				break;
				}
			});
			$form->setTitle("§8-=§6Lumberjack§8=-");
			$form->setContent("§8-=§6".$sender->getName()."§8=-\n" .
											   "§8->Level : §6".$this->getLevel($sender)."\n" . 
											   "§8->Uplift : §6".$this->getExp($sender)."/".$this->getCost($sender)."\n\n" . 
											   "§8+§6Select Menu Lumber In Below§8+");
			$form->addButton("§l§aTOP LUMBERJACK\n§r§8SEE TOP LUMBERJACK",0,"textures/ui/blindness_effect");
			$form->addButton("§l§cEXIT\n§r§8EXIT FROM MENU",0,"textures/ui/cancel");
			$form->sendToPlayer($sender);
			return $form;
	}
	
	public function TopLumber($sender){
		$level = $this->lumber->getAll();
		$msg = "";
		$message = "";
		$toplevel = "§bTopLumber";
		if(count($level) > 0){
			arsort($level);
			$i = 1;
			foreach($level as $name => $lvl){
				$msg .= "  §b[".$i."] §e".$name.": §d".$lvl." §a".$this->cfg->get("data")."\n";
				if($i >= 10){
					break;
				}
				++$i;
			}
		}
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $sender, int $data = null) {
			$result = $data;
			if($result === null){
				return true;
			}
	       switch($result){
				case 0:
				$this->Menu($sender);
				break;
				}
			});
			$form->setTitle("§8-=§6TopLumber§8=-");
			$form->setContent("".$msg);
			$form->addButton("§l§cBACK\n§r§8BACK FROM MENU",0,"textures/ui/cancel");
			$form->sendToPlayer($sender);
			return $form;
	}
	
	public function Plvl($sender){
		$p = strtolower($sender->getName());
		$this->lumber->set($p, $this->getLevel($sender));
		$this->exp->set($p, $this->getExp($sender));
		$this->cost->set($p, $this->getCost($sender));
		if($this->getExp($sender) >= $this->getCost($sender)){
			$this->addLevel($sender);
			$this->reduceExp($sender, $this->getCost($sender));
			$this->addCost($sender, 100);
			$sender->addTitle("§aLEVEL UP!");
		}
	}
	
	public function reduceExp($sender, $int){
		$p = strtolower($sender->getName());
		$this->exp->set($p, $this->exp->get($p) - $int);
		$this->exp->save();
	}
	
	public function addLevel($sender){
		$p = strtolower($sender->getName());
		$this->lumber->set($p, $this->lumber->get($p) + 1);
		$this->lumber->save();
	}
	
	public function addExp($sender, $int){
		$p = strtolower($sender->getName());
		$this->exp->set($p, $this->exp->get($p) + $int);
		$this->exp->save();
	}
	
	public function addCost($sender, $int){
		$p = strtolower($sender->getName());
		$this->cost->set($p, $this->cost->get($p) + $int);
		$this->cost->save();
	}
	
	public function getLevel($sender){
		$p = strtolower($sender->getName());
		return $this->lumber->get($p);
	}
	
	public function getExp($sender){
		$p = strtolower($sender->getName());
		return $this->exp->get($p);
	}
	
	public function getCost($sender){
		$p = strtolower($sender->getName());
		return $this->cost->get($p);
	}
}