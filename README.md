# CustomCraft

>Pocketmine plugin that allows you to add recipes for Crafting Table, Furnace, Blast Furnace, Smoker Furnace

## Recipes Usage
- Create a new crafting table recipe
```JSON
{
  "pattern": [
    "AAA",
    "AB ",
    " B "
  ],
  "key": {
    "A": {
      "item": 4,
      "data": 0
    },
    "B": {
      "item": 280,
      "data": 0
    }
  },
  "result": {
    "item": 274,
    "data": 0,
    "count": 1,
    "enchantments": {
      "unbreaking": 10,
      "efficiency": 10
    },
    "name": "Super Stone Pickaxe",
    "lore": ["&r&5Line 1", "&r&5Line 2"]
  }
}
```
- Create a new furnace recipe
```json
{
  "tags": [
    "furnace",
    "blast_furnace",
    "smoker_furnace"
  ],
  "output": {
    "item": 0,
    "data": 0
  },
  "input": {
    "item": 0,
    "data": 0
  }
}
```
- Enchantments available
```string
protection
fire_protection
feather_falling
blast_protection
projectile_protection
thorns
respiration
sharpness
knockback
fire_aspect
efficiency
silk_touch
unbreaking
power
punch
flame
infinity
mending
vanishing
fortune(must be registered with another plugin to use)
looting(must be registered with another plugin to use)
```
