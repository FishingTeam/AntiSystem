<?php

namespace AntiSystem;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Effect;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\Zombie;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Enderman;
use pocketmine\entity\Villager;
use pocketmine\entity\PigZombie;
use pocketmine\entity\Creeper;
use pocketmine\entity\Spider;
use pocketmine\entity\Witch;
use pocketmine\entity\IronGolem;
use pocketmine\entity\Blaze;
use pocketmine\entity\Slime;
use pocketmine\entity\WitherSkeleton;
use pocketmine\entity\Horse;
use pocketmine\entity\Donkey;
use pocketmine\entity\Mule;
use pocketmine\entity\SkeletonHorse;
use pocketmine\entity\ZombieHorse;
use pocketmine\entity\Stray;
use pocketmine\entity\Husk;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\format\FullChunk;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\entity\Arrow;
use pocketmine\network\protocol\PlayerActionPacket;

use pocketmine\utils\UUID;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getLogger()->info("§a>> Pluginを読み込みました。");
		$this->getLogger()->info("§b>> 不正な行為などをを検出したらすぐに報告致します。");

		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0744, true); 
		}
    		$this->getServer()->getScheduler()->scheduleRepeatingTask( new CallbackTask ( [$this,"S1"] ), 1 * 20);//spam, fly
		$this->ban = new Config($this->getDataFolder() . "ban.json",  Config::JSON,array());
		$this->player = new Config($this->getDataFolder() . "playerdata.json", Config::JSON, array());
		$this->data = $this->player->getAll();
		$this->config = new Config($this->getDataFolder() . "config.json", Config::JSON, array());
		/*if (!$this->config->exists("AntiAura")){
			$this->config->set("AntiAura", "on");
			$this->config->save();
		}*/
		if (!$this->config->exists("AntiReachHack")){
			$this->config->set("AntiReachHack", "on");
			$this->config->save();
		}
		if (!$this->config->exists("AntiReachHackMass")){
			$this->config->set("AntiReachHackMass", 5);
			$this->config->save();
		}
		if (!$this->config->exists("CommandShow")){
			$this->config->set("CommandShow", "on");
			$this->config->save();
		}
		if (!$this->config->exists("FlyKick")){
			$this->config->set("FlyKick", "on");
			$this->config->save();
		}
		if (!$this->config->exists("Itemban")){
			$this->config->set("Itemban", "on");
			$this->config->save();
		}
		if (!$this->config->exists("Spam")){
			$this->config->set("Spam", "on");
			$this->config->save();
		}
		if (!$this->config->exists("SpamTime")){
			$this->config->set("SpamTime", 3);
			$this->config->save();
		}
		if (!$this->config->exists("SteveKick")){
			$this->config->set("SteveKick", "on");
			$this->config->save();
		}
		if (!$this->config->exists("TNTLock")){
			$this->config->set("TNTLock", "on");
			$this->config->save();
		}
		/*$aura = $this->config->get("AntiAura");
		$this->Aura = $aura;*/
		$reach = $this->config->get("AntiReachHack");
		$this->ReachHack = $reach;
		$reachmass = $this->config->get("AntiReachHackMass");
		$this->ReachHackMass = $reachmass;
		$cmd = $this->config->get("CommandShow");
		$this->Command = $cmd;
		$fly = $this->config->get("FlyKick");
		$this->Fly = $fly;
		$item = $this->config->get("Itemban");
		$this->Item = $item;
		$spam = $this->config->get("Spam");
		$this->Spam = $spam;
		$spamtime = $this->config->get("SpamTime");
		$this->SpamTime = $spamtime;
		$steve = $this->config->get("SteveKick");
		$this->Steve = $steve;
		$tnt = $this->config->get("TNTLock");
		$this->TNT = $tnt;
	}

	public function onDisable(){
		$this->ban->save();
		$this->player->save();
		foreach($this->data as $t){
			$this->player->set($t["name"], $t);
		}
		$this->player->save();
	}

	public function S1(){
		$spam = $this->Spam;
		if($spam === "on"){
			foreach($this->getServer()->getOnlinePlayers() as $player){
				$fly = $this->Fly;
				if($fly === "on"){
					if($player->isFlying() === true and $player->isOp() === false and $player->getGameMode() === 0){
						$player->kick("§c不正な飛行が検出されました。",false);
						$this->getLogger()->info("§c>> ".$player->getName()." がFlyHackを使用している可能性があります。");
						$this->getLogger()->info("§e>> 状況: 警戒");
					}
				}
				$name = $player->getName();
				$name2 = strtolower($player->getName());
				$st = $this->spamtime[$name2];
				if($st > 0){
					$this->spamtime[$name2]--;
				}
			}
		}
	}

	public function onFly(PlayerToggleFlightEvent $event){
		$player = $event->getPlayer();
		$fly = $this->Fly;
		if($fly === "on"){
			if($player->isOp() === false and $player->getGameMode() === 0){
				$player->kick("§c不正な飛行が検出されました。",false);
				$this->getLogger()->info("§c>> ".$player->getName()." がFlyHackを使用している可能性があります。");
				$this->getLogger()->info("§e>> 状況: 警戒");
			}
		}
	}

	public function onDamage(EntityDamageEvent $event){
		$player = $event->getEntity();
		if ($event instanceof EntityDamageByEntityEvent) {
			$player = $event->getDamager();
			$entity = $event->getEntity();
			if ($player instanceof Player){
				$pos = new Vector3($entity->x, $entity->y, $entity->z);
				$reach = $this->ReachHack;
				$reachmass = $this->ReachHackMass;
				if ($reach === "on"){
					if ($player->distance($pos) > $reachmass and $player->getGameMode() === 0){
						//$player->kick("§cリーチが長すぎると判定されました。",false);
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onLogin(PlayerPreLoginEvent $event){	
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$nname = $player->getName();

		$this->spamtime[$name] = 0;
		$this->chatmsg[$name] = "";

		$steve = $this->Steve;
		if($steve == "on"){
			if($name === "steve"){
			$event->setKickMessage("§c名前がSteveだとログインできません。\n§c名前を変えてきてください。");
			$event->setCancelled();
			}
		}
	}

	public function loginJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$ip = $player->getAddress();
		$rawuuid = bin2hex($player->getRawUniqueId());
		$value = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
		if($value){
			$host = gethostbyaddr($ip);
		}else{
			$host = $ip;
		}
		$this->data[$name]["name"] = $name;
		$this->data[$name]["host"] = $host;
		$this->data[$name]["rawuniqueid"] = $rawuuid;
		$this->player->save();
		foreach($this->data as $t){
			$this->player->set($t["name"], $t);
		}
		$this->player->save();
	}

	public function playerban(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$ip = $player->getAddress();
		$rawuuid = bin2hex($player->getRawUniqueId());
		$value = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
		if($value){
			$host = gethostbyaddr($ip);
		}else{
			$host = "PrivateIP(".$ip.")";
		}
		if($this->ban->exists($name)){
			$event->setKickMessage("You are Banned");
			$event->setCancelled();
		}
		if($this->ban->exists($host)){
			$event->setKickMessage("You are Banned");
			$event->setCancelled();
		}
		if($this->ban->exists($rawuuid)){
			$event->setKickMessage("You are Banned");
			$event->setCancelled();
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch (strtolower($command->getName())) {
			case "sban":
			if(count($args) < 1){
				$sender->sendMessage("Usage: /sban <name>");
				return false;
			}
			$player = $this->getServer()->getPlayer($args[0]);
			if($player instanceof Player){
				$player = $player->getPlayer();
				$name = strtolower($player->getName());
				$ip = $player->getAddress();
				$rawuuid = bin2hex($player->getRawUniqueId());
				$value = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
				if($value){
					$host = gethostbyaddr($ip);
				}else{
					$host = "PrivateIP(".$ip.")";
				}
				$this->ban->set($name, $host);
				$this->ban->set($host, $rawuuid);
				$this->ban->set($rawuuid, $name);
				$this->ban->save();
				$sender->sendMessage("§e[ban] §a".$player->getName()."をbanしました。");
				$player->kick("§c[ban] あなたはbanされました。",false);
				$this->getLogger()->info("§b>> ".$sender->getName()." が ".$name." をbanしました。");
			}else{
				$name = strtolower(strtolower($args[0]));
				if(isset($this->data[$name])){
					$dhost = $this->data[$name]["host"];
					$drawuuid = $this->data[$name]["rawuniqueid"];
					$this->ban->set($name, $dhost);
					$this->ban->set($dhost, $drawuuid);
					$this->ban->set($drawuuid, $name);
					$this->ban->save();
					$sender->sendMessage("§e[ban] §a".$args[0]."をbanしました。");
					$this->getLogger()->info("§b>> ".$sender->getName()." が ".$name." をbanしました。");
				}else{
					$sender->sendMessage("§e[ban] §c".$args[0]." のデータは存在しません。");
				}
			}
			break;
			case "unsban":
			if(count($args) < 1){
				$sender->sendMessage("Usage: /unsban <name>");
				return false;
			}
			$name = strtolower(strtolower($args[0]));
			if($this->ban->exists($name)){
				$host = $this->ban->get($name);
				$rawuuid = $this->ban->get($host);
				$this->ban->remove($name);
				$this->ban->remove($host);
				$this->ban->remove($rawuuid);
				$this->ban->save();
				$sender->sendMessage("§e[ban] §a".$args[0]."のbanを解除しました");
				$this->getLogger()->info("§b>> ".$sender->getName()." が ".$name." のbanを解除しました。");
			}else{
				$sender->sendMessage("§e[ban] §c".$args[0]."はbanされていません");
			}
			return true;
			break;
		}
		return false;
	}

	public function BlockTouch(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$name2 = strtolower($player->getName());
		$item = $this->Item;
		if($item == "on"){
			if($player->getInventory()->getItemInHand()->getID() === 259){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 火打石");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 325 and $player->getInventory()->getItemInHand()->getDamage() === 8){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 水バケツ");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 325 and $player->getInventory()->getItemInHand()->getDamage() === 9){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 水バケツ");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 325 and $player->getInventory()->getItemInHand()->getDamage() === 10){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 溶岩バケツ");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 325 and $player->getInventory()->getItemInHand()->getDamage() === 11){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 溶岩バケツ");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 46){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: TNT");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 8){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 水");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 9){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 水");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 10){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 溶岩");
 				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 11){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 溶岩");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 79){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 氷");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 90){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: ネザーゲートブロック");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 95){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: 透明ブロック");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}elseif($player->getInventory()->getItemInHand()->getID() === 383){
				$event->setCancelled();
				$this->getLogger()->info("§c>> ".$name." が危険なアイテムを使用しようとしました。");
				$this->getLogger()->info("§b>> 危険なアイテム: スポーンエッグ");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}
		}
	}

	public function onEntitySpawn(EntitySpawnEvent $e){
		$entity = $e->getEntity();
		$tnt = $this->TNT;
		if($tnt == "on"){
			if($entity instanceof PrimedTNT){
				$entity->kill();
				$this->getLogger()->info("§c>> TNTが発生しました。");
				$this->getLogger()->info("§a>> 状況: 対処済み");
			}
		}
	}

	public function PlayerCommandSpam(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		$name = $player->getName();
		$name2 = strtolower($player->getName());
		$cmd = $event->getMessage();
		$spam = $this->Spam;
		$command = $this->Command;
		if($spam === "on"){
			if(strpos($event->getMessage(),'§k') !== false){
				$event->setCancelled();
			}
			if($cmd === $this->chatmsg[$name2]){
				$event->setCancelled();
			}
			if($this->spamtime[$name2] > 0){
				$event->setCancelled();
			}else{
				$this->chatmsg[$name2] = $cmd;
				if($command === "on"){
					if(substr($cmd,0,1) == "/" or substr($cmd,0,2) == "./"){
						$this->getLogger()->info("§a§o ".$user." が ".$cmd." を使用しました。");
					}
				}
			}
			$spamtime = $this->SpamTime;
			$this->spamtime[$name2] = $spamtime;
		}
	}
}
