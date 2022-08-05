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
