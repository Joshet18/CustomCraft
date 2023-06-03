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
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
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
    'swift_sneak' => EnchantmentIds::SWIFT_SNEAK,
    'fortune' => EnchantmentIds::FORTUNE,
    'looting' => EnchantmentIds::LOOTING
  ];
  private $cf;
  private int $total = 0;
  
  public function onEnable(): void {
    $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask($this->getName(), $this->getDescription()->getVersion()));
    if($this->cf->get("logger", false))$this->getLogger()->info("{$this->total} Recipes loaders");
  }
  
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
    $factory = StringToItemParser::getInstance();
    foreach(array_diff(scandir($this->getDataFolder()."Crafting_table"), ["..", "."]) as $path){
      $check = explode(".", $path);
      if($this->checkFileExtension($path)){
        if(!in_array($check[0], $this->cf->getNested("blacklist.crafting", []))){
          $config = new Config($this->getDataFolder()."/Crafting_table/{$path}", Config::JSON);
          $v = $config->getAll();
          if($this->checkCraftingData($v)){
            $recipes = [];
            foreach($v['key'] as $k => $v2){
              if(!$factory->parse($v2['item']) instanceof Item)return;
              $recipes[$k] = new ExactRecipeIngredient($factory->parse($v2['item']));
            }
            $count = 1;
            if(isset($v['result']['count']) && is_numeric($v['result']['count']) && $v['result']['count'] > 0)$count = (int)$v['result']['count'];
            $result = $factory->parse($v['result']['item'])->setCount($count);
            if(!$result instanceof Item)return;
            if(isset($v['result']['name']) && $v['result']['name'] !== "")$result->setCustomName("§r".TextFormat::colorize($v['result']['name']));
            if(isset($v['result']['enchantments']) && is_array($v['result']['enchantments']))foreach($v['result']['enchantments'] as $e => $lvl){
              $enchant = EnchantmentIdMap::getInstance()->fromId($this->getEnchantmentByName($e));
              $level = 1;
              if(is_numeric($lvl) && $lvl > 1)$level = (int)$lvl;
              if($level > 255)$level = 255;
              if($enchant !== null)$result->addEnchantment(new EnchantmentInstance($enchant, $level));
            }
            $this->total++;
            $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe($v['pattern'], $recipes, [$result]));
          }else{
            if($this->cf->get("logger", false))$this->getLogger()->error("Invalid data entered in Crafting_table/{$path}");
          }
        }
      }else{
        if($this->cf->get("logger", false))$this->getLogger()->error("Crafting_table/{$path} must be a JSON file");
      }
    }
  }
  
  public function loadFuenaceRecipes(){
    $factory = StringToItemParser::getInstance();
    foreach(array_diff(scandir($this->getDataFolder()."Furnace"), ["..", "."]) as $path){
      $check = explode(".", $path);
      if($this->checkFileExtension($path)){
        if(!in_array($check[0], $this->cf->getNested("blacklist.furnace", []))){
          $config = new Config($this->getDataFolder()."/Furnace/{$path}", Config::JSON);
          $v = $config->getAll();
          if(isset($v['tags']) && isset($v['output']) && isset($v['input']) && $this->checkData($v['input']) && $this->checkData($v['output'])){
            switch($this->checkTags($v['tags'])){
              case -1:
                if($this->cf->get("logger", false))$this->getLogger()->error("Furnace/{$path} tags must be of type array!");
              break;
              case 0:
                if($this->cf->get("logger", false))$this->getLogger()->error("Invalid tags entered in Furnace/{$path}, Tags available: [furnace, blast_furnace, smoker_furnace]");
              break;
              case 1:
                $output = $factory->parse($v['output']['item']);
                $input = $factory->parse($v['input']["item"]);
                $this->total++;
                if(!$input instanceof Item)return;
                if(!$output instanceof Item)return;
                $recipe = new FurnaceRecipe($output, new ExactRecipeIngredient($input));
                foreach($v['tags'] as $tag){
                  if(isset($this->furnacesTypes[$tag]))$this->getServer()->getCraftingManager()->getFurnaceRecipeManager($this->furnacesTypes[$tag])->register($recipe);
                }
              break;
            }
          }elseif(!isset($v['output']) or !$this->checkData($v['output'])){
            if($this->cf->get("logger", false))$this->getLogger()->error("Invalid output format entered in Furnace/{$path}");
          }elseif(!isset($v['input']) or !$this->checkData($v['input'])){
            if($this->cf->get("logger", false))$this->getLogger()->error("Invalid input format entered in Furnace/{$path}");
          }
        }
      }else{
        if($this->cf->get("logger", false))$this->getLogger()->error("Furnace/{$path} must be a JSON file");
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
        if($this->checkData($value))$keys[$k] = $k;
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
		  if(!$this->checkData($tag['result']))$v = false;
    }
    return $v;
  }
  
  private function checkFileExtension(string $path):bool{
    $extension = explode(".", $path);
    return (isset($extension[1]) && $extension[1] === "json");
  }
  
  private function checkData($tag): bool {
    if(is_array($tag) && isset($tag['item']) && is_string($tag['item']))return true;
    return false;
  }
}
