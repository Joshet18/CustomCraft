<?php

namespace Joshet18\CustomCraft;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\item\ItemIds;
use pocketmine\item\Item;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Loader extends PluginBase{
  
  public static $instance;
  private array $furnacesTypes = [];
  private array $enchantment = [
    'protection' => EnchantmentIds::PROTECTION,
    'fire_protection' => EnchantmentIds::FIRE_PROTECTION,
    'feather_falling' => EnchantmentIds::FEATHER_FALLING,
    'blast_protection' => EnchantmentIds::BLAST_PROTECTION,
    'projectile_protection' => EnchantmentIds::PROJECTILE_PROTECTION,
    'thorns' => EnchantmentIds::THORNS,
    'respiration' => EnchantmentIds::RESPIRATION,
    'sharpness' => EnchantmentIds::SHARPNESS,
    'knockback' => EnchantmentIds::KNOCKBACK,
    'fire_aspect' => EnchantmentIds::FIRE_ASPECT,
    'efficiency' => EnchantmentIds::EFFICIENCY,
    'silk_touch' => EnchantmentIds::SILK_TOUCH,
    'unbreaking' => EnchantmentIds::UNBREAKING,
    'power' => EnchantmentIds::POWER,
    'punch' => EnchantmentIds::PUNCH,
    'flame' => EnchantmentIds::FLAME,
    'infinity' => EnchantmentIds::INFINITY,
    'mending' => EnchantmentIds::MENDING,
    'vanishing' => EnchantmentIds::VANISHING,
    'fortune' => EnchantmentIds::FORTUNE,
    'looting' => EnchantmentIds::LOOTING
  ];
  private $cf;
  private int $total = 0;
  
  public function onLoad(): void {
    $this->furnacesTypes["furnace"] = FurnaceType::FURNACE();
    $this->furnacesTypes["blast_furnace"] = FurnaceType::BLAST_FURNACE();
    $this->furnacesTypes["smoker_furnace"] = FurnaceType::SMOKER();
    @mkdir($this->getDataFolder()."Crafting_table");
    @mkdir($this->getDataFolder()."Furnace");
    $this->saveResource("Crafting_table/example.json");
    $this->saveResource("Furnace/example.json");
    $this->saveResource("Config.json");
    $this->cf = new Config($this->getDataFolder()."Config.json", Config::JSON);
    $this->loadCraftingRecipes();
    $this->loadFuenaceRecipes();
  }
  
  public function onEnable(): void {
    $this->getLogger()->info("{$this->total} Recipes loaders");
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
    if($cmd->getName() === "customcraft"){
      if(!isset($args[0])){
        $sender->sendMessage("Usage: /customcraft help");
        return false;
      }
      switch($args[0]){
        case "reload":
          if($sender->hasPermission("customcraft.reload") or $sender instanceof ConsoleCommandSender){
            $this->total = 0;
            $sender->sendMessage("§aReloading recipes");
            $this->loadCraftingRecipes();
            $this->loadFuenaceRecipes();
            $sender->sendMessage("§e{$this->total} §aRecipes reloaders");
          }else{
            $sender->sendMessage("§cYou do not have permission to execute this command!");
          }
        break;
        case "help":
        case "?":
          $sender->sendMessage("§a/customcraft §ereload");
        break;
      }
    }
    return true;
  }
  
  public static function getInstance(){
    return self::$instance;
  }
  
  public function getEnchantmentByName(string $ench): int {
    return isset($this->enchantment[$ench]) === true ? $this->enchantment[$ench] : -1;
  }
  
  public function loadCraftingRecipes(){
    $factory = ItemFactory::getInstance();
    foreach(array_diff(scandir($this->getDataFolder()."Crafting_table"), ["..", "."]) as $path){
      $check = explode(".", $path);
      if(isset($check[1]) && $check[1] === "json"){
        if(!in_array($check[0], $this->cf->getNested("blacklist.crafting", []))){
          $config = new Config($this->getDataFolder()."/Crafting_table/{$path}", Config::JSON);
          $v = $config->getAll();
          if($this->checkCraftingData($v)){
            $recipes = [];
            foreach($v['key'] as $k => $v2){
              $recipes[$k] = $factory->get($v2['item'], $v2['data'], 1);
            }
            $count = 1;
            if(isset($v['result']['count']) && is_numeric($v['result']['count']))$count = $v['result']['count'];
            $result = $factory->get($v['result']['item'], $v['result']['data'], $count);
            if(isset($v['result']['name']) && $v['result']['name'] !== "")$result->setCustomName("§r".TextFormat::colorize($v['result']['name']));
            foreach($v['result']['enchantments'] as $e => $lvl){
              $enchant = EnchantmentIdMap::getInstance()->fromId($this->getEnchantmentByName($e));
              if($enchant !== null)$result->addEnchantment(new EnchantmentInstance($enchant, $lvl));
            }
            $this->total++;
            $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe($v['pattern'], $recipes, [$result]));
          }else{
            $this->getLogger()->error("§cInvalid data entered in Crafting_table/{$path}");
          }
        }
      }else{
        $this->getLogger()->error("§cCrafting_table/{$path} must be a JSON file (Recipe not loaded for security)");
      }
    }
  }
  
  public function loadFuenaceRecipes(){
    $factory = ItemFactory::getInstance();
    foreach(array_diff(scandir($this->getDataFolder()."Furnace"), ["..", "."]) as $path){
      $check = explode(".", $path);
      if(isset($check[1]) && $check[1] === "json"){
        if(!in_array($check[0], $this->cf->getNested("blacklist.furnace", []))){
          $config = new Config($this->getDataFolder()."/Furnace/{$path}", Config::JSON);
          $v = $config->getAll();
          if(isset($v['tags']) && isset($v['output']) && isset($v['input']) && $this->checkFurnaceData($v['input']) && $this->checkFurnaceData($v['output'])){
            switch($this->checkTags($v['tags'])){
              case -1:
                $this->getLogger()->error("§cFurnace/{$path} tags must be of type array!");
              break;
              case 0:
                $this->getLogger()->error("§cInvalid tags entered in Furnace/{$path}, Tags available: [furnace, blast_furnace, smoker_furnace]");
              break;
              case 1:
                $this->total++;
                $recipe = new FurnaceRecipe($factory->get($v['output']['item'], $v['output']['data'], 1), $factory->get($v['input']["item"], $v['input']['data'], 1));
                foreach($v['tags'] as $tag){
                  if(isset($this->furnacesTypes[$tag]))$this->getServer()->getCraftingManager()->getFurnaceRecipeManager($this->furnacesTypes[$tag])->register($recipe);
                }
              break;
            }
          }elseif(!isset($v['output']) or !$this->checkFurnaceData($v['output'])){
            $this->getLogger()->error("§cInvalid output format entered in Furnace/{$path}");
          }elseif(!isset($v['input']) or !$this->checkFurnaceData($v['input'])){
            $this->getLogger()->error("§cInvalid input format entered in Furnace/{$path}");
          }
        }
      }else{
        $this->getLogger()->error("§cFurnace/{$path} must be a JSON file (Recipe not loaded for security)");
      }
    }
  }
  
  private function checkTags($tags): int {
    $result = -1;
    if(is_array($tags)){
      if(!in_array("furnace", $tags) && !in_array("blast_furnace", $tags) && !in_array("smoker_furnace", $tags))$result = 0;
      if(in_array("furnace", $tags) or in_array("blast_furnace", $tags) or in_array("smoker_furnace", $tags))$result = 1;
    }
    return $result;
  }
  
  private function checkCraftingData($tag): bool {
    $v = false;
    $keys = [];
    if(isset($tag['pattern']) && is_array($tag['pattern']) && isset($tag['key']) && is_array($tag['key']) && isset($tag['result']) && is_array($tag['result'])){
      $height = count($tag['pattern']);
      if($height > 3 || $height <= 0){
        return false;
      }
      $shape = array_values($tag['pattern']);
      $width = strlen($shape[0]);
      if($width > 3 || $width <= 0){
        return false;
      }
      foreach($tag['key'] as $k => $value){
        if($this->checkFurnaceData($value))$keys[$k] = $k;
      }
      foreach($shape as $n => $row){
        if(strlen($row) !== $width){
          return false;
        }
        for($x = 0; $x < $width; ++$x){
          if($row[$x] !== ' ' && !isset($keys[$row[$x]])){
            return false;
          }
				}
			}
      foreach($keys as $char => $l){
        if(strpos(implode($shape), $char) === false){
          return false;
        }
			  $v = true;
		  }
		  if(!$this->checkFurnaceData($tag['result']))$v = false;
    }
    return $v;
  }
  
  private function checkFurnaceData($tag): bool {
    if(is_array($tag) && isset($tag['item']) && isset($tag['data']) && is_numeric($tag['item']) && is_numeric($tag['data']))return true;
    return false;
  }
}
