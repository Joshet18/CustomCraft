[![](https://poggit.pmmp.io/shield.state/CustomCraft)](https://poggit.pmmp.io/p/CustomCraft)
[![](https://poggit.pmmp.io/shield.api/CustomCraft)](https://poggit.pmmp.io/p/CustomCraft)
[![](https://poggit.pmmp.io/shield.dl.total/CustomCraft)](https://poggit.pmmp.io/p/CustomCraft)

## 🔧 Setup:
1) Create json file in plugin folder (Furnace or Crafting_table)
2) Edit the format using the item's id/meta
3) Use the command /customcraft reload or restart the server to load all recipes 

## 📜 Formats:
- Crafting Table:
```JSON
{
  "pattern": [
    "AAA",
    "AB ",
    " B "
  ],
  "key": {
    "A": {
      "item": "minecraft:cobble"
    },
    "B": {
      "item": "minecraft:stick"
    }
  },
  "result": {
    "item": "minecraft:stone_pickaxe"
    "count": 1,
    "enchantments": {
      "unbreaking": 10,
      "efficiency": 10
    },
    "name": "Super Stone Pickaxe"
  }
}
```
- Furnace:
```json
{
  "tags": [
    "furnace",
    "blast_furnace",
    "smoker_furnace"
  ],
  "output": {
    "item": "minecraft:cobble"
  },
  "input": {
    "item": "minecraft:stick"
  }
}
```
## 🗒️ Items List:

- [Click](https://github.com/Joshet18/CustomCraft/blob/main/ItemsIds.md) to show
##

## 📋 Permissions:
| Permission         | Command                |
|--------------------|------------------------|
| customcraft        | `/customcraft`         |
| customcraft.reload | `/customcraft reload`  |


## 📖 Enchantments available:

| Name                  | Other plugin register require|
|-----------------------|------------------------------|
| protection            | ❌                           |
| fire_protection       | ❌                           |
| blast_protection      | ❌                           |
| projectile_protection | ❌                           |
| feather_falling       | ❌                           |
| thorns                | ❌                           |
| respiration           | ❌                           |
| sharpness             | ❌                           |
| knockback             | ❌                           |
| fire_aspect           | ❌                           |
| efficiency            | ❌                           |
| silk_touch            | ❌                           |
| unbreaking            | ❌                           |
| power                 | ❌                           |
| punch                 | ❌                           |
| flame                 | ❌                           |
| infinity              | ❌                           |
| mending               | ❌                           |
| vanishing             | ❌                           |
| swift_sneak           | ❌                           |
| fortune               | ✔️                           |
| looting               | ✔️                           |
