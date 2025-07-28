<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HiddenLeaders implementation : © Hervé DI STEFANO hdistef7@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * HiddenLeaders game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->END_GAME_NB_VISIBLECARDS = [

  2 => 8,
  3 => 7,
  4 => 7,
  5 => 6,
  6 => 5
  
];

$this->SPREAD_CORRUPTION = [

  2 => [2 => 3,1 => 5],
  3 => [2 => 3,1 => 5],
  4 => [2 => 3,1 => 5],
  5 => [2 => 3,1 => 5],
  6 => [2 => 3,1 => 4],
  
];

$this->FACTIONS = [

  UNDEAD => clienttranslate('Undead'),
  WATER_FOLK => clienttranslate('Water Folk'),
  EMPIRE => clienttranslate('Imperial Army'),
  TRIBES => clienttranslate('Hill Tribes'),
  GUARDIAN => clienttranslate('Guardians'),
  EMPEROR => clienttranslate('All 4 factions'),

];

$this->CARD_TYPE = [

  LEADER => clienttranslate('Leader'),
  HERO => clienttranslate('Hero')

];

$this->LOCATIONS = [

  HARBOR => 'deck',
  WILDERNESS => 'discard',
  GRAVEYARD => 'graveyard'
];


$this->FATE_CARDS = [
  1 => [GUARDIAN => -2],
  2 =>[EMPIRE => -1],
  3 => [TRIBES => -1],
  4 =>[GUARDIAN => -1],
  5 =>[GUARDIAN => -2],
  6 =>[GUARDIAN => -2],
];

$this->CORRUPTION_TOKENS = [
  'x2','empire', 'tribes', 'undead', 'water_folk', 'guardian'
];

$this->ARTIFACT_CARDS = [
  1 => new ArtifactCard(1,clienttranslate('All-Knowing Antler'), 'AllKnowingAntler', 2, 1, SLOW,
  clienttranslate('${slow}: Shuffle the ${wilderness}. Search the ${wilderness} and take any 1 card into your hand. Then shuffle the ${wilderness} again')),

  3 => new ArtifactCard(3,clienttranslate('Shield of Endurance'), 'ShieldOfEndurance', 2, 2, '',
  clienttranslate('Whenever: any 1 ${visible} OR ${hidden} would be buried, you may use 1 of your ${artifact} tokens to prevent this')),

  5 => new ArtifactCard(5,clienttranslate('Sedative Shell'), 'SedativeShell', 3, 3, SLOW,
  clienttranslate('${slow}: Exchange 1 ${hidden} in your party with 1 card in your hand')),

  7 => new ArtifactCard(7,clienttranslate('Trapping Treasure'), 'TrappingTreasure', 3, 4, FAST,
  clienttranslate('${fast}: Exchange 1 card from ${tavern} with any 1 ${visible}')),

  9 => new ArtifactCard(9,clienttranslate('Soundless Shoes'), 'SoundlessShoes', 2, 5, FAST,
  clienttranslate('${fast}: Place a card from your hand into your party ${hidden}')),

  11 => new ArtifactCard(11,clienttranslate('Guarding Goblet'), 'GuardingGoblet', 2, 6, SLOW,
  clienttranslate('${slow}: Take any 1 ${visible} ${guardian} into your hand')),
  
  2 => new ArtifactCard(2,clienttranslate('Blooming Bag'), 'BloomingBag', 3, 1, SLOW,
  clienttranslate('${slow}: Draw a card from ${tavern} OR from ${graveyard}')),

  4 => new ArtifactCard(4,clienttranslate('2-Shot Crossbow'), 'TwoShotCrossbow', 2, 2, FAST,
  clienttranslate('${fast}: Bury any 1 ${visible} OR ${hidden}')),

  6 => new ArtifactCard(6,clienttranslate('Overcharged Trident'), 'OverchargedTrident', 2, 3, FAST,
  clienttranslate('${fast}: Pick 1 of your ${visible}
  <br>
  Perform that ${visible} abilities as if you played it. Then bury it')),

  8 => new ArtifactCard(8,clienttranslate('Suggestive Puppet'), 'SuggestivePuppet', 2, 4, FAST,
  clienttranslate('${fast}: Pick 1 card in ${tavern}. Perform that card\'s abilities as if you played it.
  <br>
  Then place it ${hidden} in another ${player} party')),

  10 => new ArtifactCard(10,clienttranslate('Confusing Crystal'), 'ConfusingCrystal', 3, 5, FAST,
  clienttranslate('${fast}: Look at 2 ${hidden}. You may place 1 of them ${hidden} into the party of a ${player} other than you')),
  
  12 => new ArtifactCard(12,clienttranslate('Nutritious Stew'), 'NutritiousStew', 3, 6, FAST,
  clienttranslate('${fast}: Take 1 ${corruption} from the bag and place it onto any 1 ${visible} OR ${hidden}')),
  
];

$this->LEADER_CARDS = [

  LEMRON => new LeaderCard(
    clienttranslate('Lemron - The Wise'), 
    array(WATER_FOLK, TRIBES), 
    clienttranslate('Although Lemron lost her sight, she never turned a blind eye to the countless injustices of the Empire'), 
    1),
  CYRA => new LeaderCard(
    clienttranslate('Cyra - The Righteous'), 
    array(EMPIRE, WATER_FOLK), 
    clienttranslate('As a general, Cyra follows in the footsteps of her father. She wants to restore order and peace within Oshra'), 
    2),
  MYRAD => new LeaderCard(
    clienttranslate('Myrad - The Banished'), 
    array(UNDEAD, TRIBES), 
    clienttranslate('Once Myrad challenged the broken promises of his father. Defeated, he is gathering an army for his revenge'), 
    3),
  XIADUL => new LeaderCard(
    clienttranslate('Xiadul - The Cunning'), 
    array(EMPIRE, UNDEAD), 
    clienttranslate('As child of the streets, Xiadul has no claim to the throne. As a compensation, he seized half the treasury'), 
    4),
  PAVYR => new LeaderCard(
    clienttranslate('Pavyr - The Opportunist'), 
    array(EMPIRE, TRIBES), 
    clienttranslate('As a well-traveled man, Pavyr\'s ties within Oshra are weak. He will ally with anyone to claim the throne'), 
    5),
  ENNED => new LeaderCard(
    clienttranslate('Enned - The Innocent'), 
    array(UNDEAD, WATER_FOLK), 
    clienttranslate('Enned was raised to be a diplomat. However, due to her young age she is easily frustrated'), 
    6)

];

$this->FL_LEADER_CARDS = [

  TISSA => new LeaderCard(
    clienttranslate('Tissa - The Inventive'), 
    array(EMPIRE, GUARDIAN), 
    clienttranslate('Tissa once secretly created the first mechanical being. Since the Mother\'s return, she is acting more openly'), 
    7),
  ULC => new LeaderCard(
    clienttranslate('Ulc - The Rooted'), 
    array(TRIBES, GUARDIAN), 
    clienttranslate('Long ago, Ulc merged his life force with an ancient tree. But, he also began to share its anger'), 
    8),
  IRAFEL => new LeaderCard(
    clienttranslate('Irafel - The Lyrical'), 
    array(UNDEAD, GUARDIAN), 
    clienttranslate('Irafel listened to the songs from below. When they began to sing, the dead began to follow them'), 
    9),
  VYMA => new LeaderCard(
    clienttranslate('Vyma - The Deep'), 
    array(WATER_FOLK, GUARDIAN), 
    clienttranslate('As a high priestess of the Water Folk, the most powerful creatures of the deep obey Vyma\'s command'), 
    10)

];

$this->HERO_CARDS = [
  // HERO CARDS
  // TRIBES
  HERO_1 => [
    "name" => clienttranslate('Shaky Sharpshooter'),
    "class" => 'ShakySharpshooter',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${visible} ${empire}'),
    "token_action" => [1=> 1, 2 => -1, 3 => TOKEN_OR],
  ],
  HERO_2 => [
    "name" => clienttranslate('Overworked Amazon'),
    "class" => 'OverworkedAmazon',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}. They have to bury 1 of their ${visible}'),
    "token_action" => [1=> 1, 2 => -1, 3 => TOKEN_OR],
  ],
  HERO_3 => [
    "name" => clienttranslate('Hangry Barbarian'),
    "class" => 'HangryBarbarian',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-X ${empire}'),
    "description" => clienttranslate('Discard all ${empire} AND ${undead} from ${tavern}
    <br>
    X is the number of cards discarded.'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_4 => [
    "name" => clienttranslate('Joyless Chief'),
    "class" => 'JoylessChief',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire}'),
    "description" => clienttranslate('Look at the top 2 cards from ${wilderness}
    <br>
    Place 1 of them into your party ${hidden}'),
    "token_action" => [1=> 1, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_5 => [
    "name" => clienttranslate('Battle Pet Master'),
    "class" => 'BattlePetMaster',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Place 1 card from your hand into your party ${hidden}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_6 => [
    "name" => clienttranslate('Curious Troll'),
    "class" => 'CuriousTroll',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('You may look at any 2 ${hidden}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
    "canPass" => 1
  ],
  HERO_7 => [
    "name" => clienttranslate('Pigmented War Pig'),
    "class" => 'PigmentedWarPig',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} AND +1 ${tribes}'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_8 => [
    "name" => clienttranslate('Bored Goblin'),
    "class" => 'BoredGoblin',
    "faction_id" => TRIBES,
    "description" => clienttranslate('-2 ${empire} IF you have 1 or more ${visible} ${undead} in your party'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_9 => [
    "name" => clienttranslate('Curious Cat Lover'),
    "class" => 'CuriousCatLover',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire}'),
    "description" => clienttranslate('Draw 1 card from another ${player} hand. Place it in your party ${hidden}'),
    "token_action" => [1=> 1, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_10 => [
    "name" => clienttranslate('Watchful Witch'),
    "class" => 'WatchfulWitch',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('You may look at any 2 ${hidden}'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_OR],
    "canPass" => 1
  ],
  HERO_11 => [
    "name" => clienttranslate('Depressed Druid'),
    "class" => 'DepressedDruid',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-3 ${empire} AND -1 ${tribes}'),
    "token_action" => [1=> -3, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_12 => [
    "name" => clienttranslate('Blind Eye Collector'),
    "class" => 'BlindEyeCollector',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire} AND +3 ${tribes}'),
    "token_action" => [1=> 1, 2 => 3, 3 => TOKEN_AND],
  ],
  HERO_13 => [
    "name" => clienttranslate('Hairy Hermit'),
    "class" => 'HairyHermit',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire}'),
    "description" => clienttranslate('OR -2 ${empire} IF the ${empire} marker is the leading marker'),
  ],
  HERO_14 => [
    "name" => clienttranslate('Long-Eared Loner'),
    "class" => 'LongEaredLoner',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${tribes}'),
    "description" => clienttranslate('Pick another ${player}
    <br>
    Turn over 1 of their ${visible} OR ${hidden}'),
    "token_action" => [1=> 0, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_15 => [
    "name" => clienttranslate('Saber-Toothed Troll'),
    "class" => 'SaberToothedTroll',
    "faction_id" => TRIBES,
    "description" => clienttranslate('Take a ${visible} from another ${player} into your hand
    <br>
    They decide: +2 ${tribes} OR -2 ${tribes}'),
  ],
  HERO_16 => [
    "name" => clienttranslate('Potato Privateer'),
    "class" => 'PotatoPrivateer',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+X ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}. X is the number of ${visible} ${empire} in their party (max +3)'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_17 => [
    "name" => clienttranslate('Spirited Shaman'),
    "class" => 'SpiritedShaman',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('Pick another ${player}
    <br>
    Turn over 1 of their ${visible} OR ${hidden}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_18 => [
    "name" => clienttranslate('Grumpy Guard'),
    "class" => 'GrumpyGuard',
    "faction_id" => TRIBES,
    "description" => clienttranslate('+2 ${tribes} IF you have 1 or more ${visible} ${water_folk} in your party'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],

  // empire
  HERO_19 => [
    "name" => clienttranslate('Short-Sighted Soldier'),
    "class" => 'ShortSightedSoldier',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} AND -1 ${tribes}'),
    "token_action" => [1=> 1, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_20 => [
    "name" => clienttranslate('Doubtful Priest'),
    "class" => 'DoubtfulPriest',
    "faction_id" => EMPIRE,
    "description" => clienttranslate('${player} next in turn decides:
    <br>
    +2 ${empire} OR -2 ${empire}
    <br>
    You may play another card that is not ${empire}'),
  ],
  HERO_21 => [
    "name" => clienttranslate('Underestimated Squire'),
    "class" => 'UnderestimatedSquire',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('You may exchange 1 of your ${hidden} with 1 card from your hand'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
    "canPass" => 1
  ],
  HERO_22 => [
    "name" => clienttranslate('Flailing Knight'),
    "class" => 'FlailingKnight',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire} AND -3 ${tribes}'),
    "token_action" => [1=> -1, 2 => -3, 3 => TOKEN_AND],
  ],
  HERO_23 => [
    "name" => clienttranslate('Underpaid Mercenary'),
    "class" => 'UnderpaidMercenary',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+3 ${empire} AND +1 ${tribes}'),
    "token_action" => [1=> 3, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_24 => [
    "name" => clienttranslate('Heart-Bending Bard'),
    "class" => 'HeartBendingBard',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-X ${tribes}'),
    "description" => clienttranslate('Discard all ${water_folk} AND ${tribes} from ${tavern}
    <br>
    X is the number of cards discarded.'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_25 => [
    "name" => clienttranslate('Modest Monsterslayer'),
    "class" => 'ModestMonsterslayer',
    "faction_id" => EMPIRE,
    "description" => clienttranslate('Pick 1 card from ${tavern}. Perform that card\'s abilities as if you played it. Then place it into your party ${hidden}'),
  ],
  HERO_26 => [
    "name" => clienttranslate('Ace Fighter'),
    "class" => 'AceFighter',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire}'),
    "description" => clienttranslate('OR +2 ${empire} IF the ${tribes} marker is the leading marker'),
  ],
  HERO_27 => [
    "name" => clienttranslate('Battle Connoisseur'),
    "class" => 'BattleConnoisseur',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${tribes}'),
    "description" => clienttranslate('Take all cards from ${tavern}.
    <br>
    Place 1 of them into your party ${hidden}. Discard the others'),
    "token_action" => [1=> 0, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_28 => [
    "name" => clienttranslate('Canned Champion'),
    "class" => 'CannedChampion',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${visible} ${tribes}'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_29 => [
    "name" => clienttranslate('Nagging Northman'),
    "class" => 'NaggingNorthman',
    "faction_id" => EMPIRE,
    "description" => clienttranslate('+2 ${empire} IF you discard a ${tribes} from ${tavern}'),
  ],
  HERO_30 => [
    "name" => clienttranslate('Almost-Evil Scholar'),
    "class" => 'AlmostEvilScholar',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+X ${empire}'),
    "description" => clienttranslate('Pick 1 ${player}. X is the number of ${visible} ${tribes} in their party (max +3)'),
  ],
  HERO_31 => [
    "name" => clienttranslate('Well-Aged Warrior'),
    "class" => 'WellAgedWarrior',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Place 1 card from your hand into your party ${hidden}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_32 => [
    "name" => clienttranslate('Androgynous Assassin'),
    "class" => 'AndrogynousAssassin',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Guess 1 faction : Turn over 1 ${hidden} of another ${player}. Bury that ${hidden} IF you guessed correctly'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_33 => [
    "name" => clienttranslate('Groggy Preacher'),
    "class" => 'GroggyPreacher',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 card from ${tavern} with 1 of your ${visible}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_34 => [
    "name" => clienttranslate('Queer Quartermaster'),
    "class" => 'QueerQuartermaster',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire}'),
    "description" => clienttranslate('You may exchange 1 of your ${visible} that is not ${empire} with 1 card in your hand'),
    "token_action" => [1=> 1, 2 => 0, 3 => TOKEN_AND],
    "canPass" => 1
  ],
  HERO_35 => [
    "name" => clienttranslate('Angry Priestess'),
    "class" => 'AngryPriestess',
    "faction_id" => EMPIRE,
    "description" => clienttranslate('-2 ${tribes} IF you discard a ${undead} from ${tavern}'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_36 => [
    "name" => clienttranslate('Resilient Rearguard'),
    "class" => 'ResilientRearguard',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Draw 1 card from another ${player} hand'),
    "token_action" => [1=> 1, 2 => -1, 3 => TOKEN_OR],
  ],

  // UNDEAD
  HERO_37 => [
    "name" => clienttranslate('Arrowgant Skeleton'),
    "class" => 'ArrowgantSkeleton',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} AND/OR +2 ${tribes}'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_AND_OR],
  ],
  HERO_38 => [
    "name" => clienttranslate('Unconfident Executioner'),
    "class" => 'UnconfidentExecutioner',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${visible}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_39 => [
    "name" => clienttranslate('Ghastly Granny'),
    "class" => 'GhastlyGranny',
    "faction_id" => UNDEAD,
    "description" => clienttranslate('Pick 1 of your ${visible}
    <br>
    Perform that card\'s abilities as if you played it'),
  ],
  HERO_40 => [
    "name" => clienttranslate('Rotting Orangutan'),
    "class" => 'RottingOrangutan',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} OR +2 ${tribes}'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_OR],
  ],
  HERO_41 => [
    "name" => clienttranslate('Half-Headed Wizard'),
    "class" => 'HalfHeadedWizard',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 card from ${tavern} with 1 ${visible} of another ${player}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_42 => [
    "name" => clienttranslate('Nightmarish Northman'),
    "class" => 'NightmarishNorthman',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} AND/OR +2 ${tribes}'),
    "description" => clienttranslate('Bury 1 of your ${visible}
    <br>
    AND 1 ${visible} of another ${player}'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_AND_OR],
  ],
  HERO_43 => [
    "name" => clienttranslate('Half-Eaten Bull'),
    "class" => 'HalfEatenBull',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${visible} ${water_folk}'),
    "token_action" => [1=> 2, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_44 => [
    "name" => clienttranslate('Gorgeous Gorgon'),
    "class" => 'GorgeousGorgon',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}. Bury 1 of their ${visible} at random'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_45 => [
    "name" => clienttranslate('Crow Carrier'),
    "class" => 'CrowCarrier',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('Draw 2 cards from ${graveyard}
    <br>
    Place 1 of them into your party ${hidden}. Discard the other'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_46 => [
    "name" => clienttranslate('Insidious Impaler'),
    "class" => 'InsidiousImpaler',
    "faction_id" => UNDEAD,
    "description" => clienttranslate('Pick 1 card in ${tavern}
    <br>
    Perform that card\'s abilities as if you played it. Then bury it'),
  ],
  HERO_47 => [
    "name" => clienttranslate('Lethargic Leech'),
    "class" => 'LethargicLeech',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Place 1 card from your hand into your party ${hidden}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_OR],
  ],
  HERO_48 => [
    "name" => clienttranslate('Will-Bending Witch'),
    "class" => 'WillBendingWitch',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('All other ${player} have to discard 1 card from their hand'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_49 => [
    "name" => clienttranslate('Resurrected Ram'),
    "class" => 'ResurrectedRam',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Discard all cards from ${tavern}. Then refill it with the top cards from ${graveyard}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_50 => [
    "name" => clienttranslate('Mummy Mystic'),
    "class" => 'MummyMystic',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} AND +2 ${tribes}'),
    "description" => clienttranslate('You may exchange the top card from ${graveyard} with 1 of your ${visible}'),
    "token_action" => [1=> 1, 2 => 2, 3 => TOKEN_AND],
    "canPass" => 1
  ],
  HERO_51 => [
    "name" => clienttranslate('Naughty Necromancer'),
    "class" => 'NaughtyNecromancer',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+X ${empire} AND +X ${tribes}'),
    "description" => clienttranslate('X is the number of cards in ${graveyard} (max +3)'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_52 => [
    "name" => clienttranslate('Sun-Shy Skeleton'),
    "class" => 'SunShySkeleton',
    "faction_id" => UNDEAD,
    "description" => clienttranslate('Perform the abilities of the top card from ${graveyard} as if you played it'),
  ],
  HERO_53 => [
    "name" => clienttranslate('Wrapped Warrior'),
    "class" => 'WrappedWarrior',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Take any 1 card from ${graveyard} into your hand'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_54 => [
    "name" => clienttranslate('Slaughtered Slime'),
    "class" => 'SlaughteredSlime',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${hidden}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_OR],
  ],

  // WATERFOLK
  HERO_55 => [
    "name" => clienttranslate('Pessimistic Whaleman'),
    "class" => 'PessimisticWhaleman',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Draw 2 cards from ${harbor}
    <br>
    Place 1 of them into your party ${hidden}. Discard the other'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_56 => [
    "name" => clienttranslate('Tentacle Oracle'),
    "class" => 'TentacleOracle',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('Reveal the top card from ${wilderness}
    <br>
    -3 ${empire} AND -3 ${tribes} IF that card is not ${undead}'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_57 => [
    "name" => clienttranslate('Drowned Deserter'),
    "class" => 'DrownedDeserter',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('IF there is a leading marker,
    <br>
    -1 leading marker OR +2 trailing marker'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_OR],
  ],
  HERO_58 => [
    "name" => clienttranslate('Deep Sea Squire'),
    "class" => 'DeepSeaSquire',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Draw 2 cards from ${harbor}
    <br>
    Place 1 of them into your party ${hidden}. Keep 1 in hand'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_59 => [
    "name" => clienttranslate('Vegetarian Sharkguard'),
    "class" => 'VegetarianSharkguard',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+2 ${empire} AND/OR +2 ${tribes}'),
    "description" => clienttranslate('Discard 1 ${visible} of another ${player}'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_AND_OR],
  ],
  HERO_60 => [
    "name" => clienttranslate('Double-Shielded Turtle'),
    "class" => 'DoubleShieldedTurtle',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-X ${empire} AND/OR -X ${tribes}'),
    "description" => clienttranslate('Pick 1 faction : Discard all cards of this faction from ${tavern}
    <br>
    X is the number of cards discarded.'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND_OR],
  ],
  HERO_61 => [
    "name" => clienttranslate('Leery Lizard'),
    "class" => 'LeeryLizard',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-1 ${empire} OR -2 ${tribes}'),
    "token_action" => [1=> -1, 2 => -2, 3 => TOKEN_OR],
  ],
  HERO_62 => [
    "name" => clienttranslate('Furious Frog'),
    "class" => 'FuriousFrog',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-2 ${empire} AND/OR -2 ${tribes}'),
    "token_action" => [1=> -2, 2 => -2, 3 => TOKEN_AND_OR],
  ],
  HERO_63 => [
    "name" => clienttranslate('Apathetic Waterpriest'),
    "class" => 'ApatheticWaterpriest',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-2 ${empire} OR -1 ${tribes}'),
    "token_action" => [1=> -2, 2 => -1, 3 => TOKEN_OR],
  ],
  HERO_64 => [
    "name" => clienttranslate('Hopeful Salamander'),
    "class" => 'HopefulSalamander',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Take 1 card from ${tavern}
    <br>
    Place it in your party ${visible}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_65 => [
    "name" => clienttranslate('Keen Koi'),
    "class" => 'KeenKoi',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('IF there is a leading marker,
    <br>
    -1 leading marker AND +1 trailing marker'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_66 => [
    "name" => clienttranslate('Triple Sword Lizard'),
    "class" => 'TripleSwordLizard',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('IF there is a leading marker,
    <br>
    -1 leading marker OR -3 leading marker'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_OR],
  ],
  HERO_67 => [
    "name" => clienttranslate('Saltwater Sage'),
    "class" => 'SaltwaterSage',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('Turn over 1 of your ${hidden}
    <br>
    Perform that card\'s abilities as if you played it'),
  ],
  HERO_68 => [
    "name" => clienttranslate('Miniature Merman'),
    "class" => 'MiniatureMerman',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Draw 2 cards either from ${wilderness} OR ${graveyard}'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_69 => [
    "name" => clienttranslate('Krill Keeper'),
    "class" => 'KrillKeeper',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-X ${empire} AND -X ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}
    <br>
    X is the number of ${visible} ${undead} in their party (max -3)'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_70 => [
    "name" => clienttranslate('Aimless Eel'),
    "class" => 'AimlessEel',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Bury any 1 ${visible} ${undead}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_71 => [
    "name" => clienttranslate('Bludgeoning Blowfish'),
    "class" => 'BludgeoningBlowfish',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Turn over 1 ${hidden} of another ${player}
    <br>
    OR look at any 1 ${hidden}'),
    "token_action" => [1=> 1, 2 => -1, 3 => TOKEN_OR],
  ],
  HERO_72 => [
    "name" => clienttranslate('Friendly Frogmage'),
    "class" => 'FriendlyFrogmage',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Place 1 card from your hand into your party ${hidden}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  // EMPEROR
  HERO_EMPEROR => [
    "name" => clienttranslate('Buried Emperor'),
    "class" => 'BuriedEmperor',
    "faction_id" => EMPEROR,
    "description" => clienttranslate('this card is 1 Hero representing all factions at any time'),
    "isYellow" => 1
  ],

  //KICKSTARTER
  HERO_73 => [
    "name" => clienttranslate('Gambling Overseer'),
    "class" => 'GamblingOverseer',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('Bury 1 of your ${hidden}
    <br>
    Then place 2 cards from your hand into your party ${hidden}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
  ],
  HERO_74 => [
    "name" => clienttranslate('Goblin Crytographer'),
    "class" => 'GoblinCrytographer',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire} AND/OR -1 ${tribes}'),
    "description" => clienttranslate('You may take 1 ${visible} and place it in the party of a ${player} other than you'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND_OR],
    "canPass" => 1
  ],
  HERO_75 => [
    "name" => clienttranslate('Careless Cartographer'),
    "class" => 'CarelessCartographer',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} AND/OR +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 ${hidden} of another ${player} with 1 of your ${hidden}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND_OR],
  ],
  HERO_76 => [
    "name" => clienttranslate('Underwater Artist'),
    "class" => 'UnderwaterArtist',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Choose 1 faction and 1 ${player}
    <br>
    If able, ${player} reveals 1 card of that faction from their hand. Add it to your hand'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
  ],
  HERO_77 => [
    "name" => clienttranslate('Polar Protector'),
    "class" => 'PolarProtector',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-X ${empire} OR +X ${tribes}'),
    "description" => clienttranslate('X is the number of ${hidden} in your party (max +/-3)'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_OR],
  ],
  HERO_78 => [
    "name" => clienttranslate('Firm Fishmonger'),
    "class" => 'FirmFishmonger',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} AND/OR +1 ${tribes}'),
    "description" => clienttranslate('Look at the top 3 cards from ${wilderness}
    <br>
    Exchange 1 of them with 1 of your ${hidden}'),
    "token_action" => [1=> -1, 2 => 1, 3 => TOKEN_AND_OR],
  ],
  HERO_79 => [
    "name" => clienttranslate('Sanguine Scholar'),
    "class" => 'SanguineScholar',
    "faction_id" => EMPIRE,
    "description" => clienttranslate('Pick 1 ${visible} from the next ${player}
    <br>
    Perform this card as if you played it'),
  ],
  HERO_80 => [
    "name" => clienttranslate('Well-Shaved Wizard'),
    "class" => 'WellShavedWizard',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+X ${empire} OR -X ${tribes}'),
    "description" => clienttranslate('X is the number of factions other than ${empire} in your party'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_OR],
  ],
  HERO_81 => [
    "name" => clienttranslate('Half-Sliced Ghoul'),
    "class" => 'HalfSlicedGhoul',
    "faction_id" => UNDEAD,
    "description" => clienttranslate('Turn over 1 ${hidden} of another ${player}
    <br>
    IF this card is ${empire} or ${tribes} :
    <br>
    +2 ${empire} AND/OR +2 ${tribes}'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_AND_OR],
  ],
  HERO_82 => [
    "name" => clienttranslate('Kind King Slayer'),
    "class" => 'KindKingSlayer',
    "faction_id" => UNDEAD,
    "description" => clienttranslate('IF there is a leading marker, -2 leading marker.
    <br>
    Bury any 1 ${hidden} or ${visible}'),
    "token_action" => [1=> 0, 2 => 0, 3 => TOKEN_AND],
  ],
  HERO_83 => [
    "name" => clienttranslate('Careful Chameleon'),
    "class" => 'CarefulChameleon',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 card from your hand with 1 ${visible} of another ${player}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
  ],
  HERO_84 => [
    "name" => clienttranslate('Seaweed Chopper'),
    "class" => 'SeaweedChopper',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('Draw 2 cards from ${wilderness}
    <br>
    Put 1 of them into your party ${hidden}. Keep 1 in hand'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_OR],
  ],
];
  
$this->QUEENS_CARDS = [
  //QUEENS && BEST FRIEND

  KeeperOfDiscord => [
    "name" => clienttranslate('Keeper of Discord'),
    "class" => 'KeeperOfDiscord',
    "faction_id" => 0,
    "description_token" => clienttranslate('-1 ${empire} AND/OR -1 ${tribes}'),
    "description" => clienttranslate('While this card is ${visible} in your party:
    <br>
    IF another ${visible} in your party would be buried, it is turned over instead'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND_OR],
    "isYellow" => 1
  ],
  EmperorBestFriend => [
    "name" => clienttranslate('Emperor\'s Best Friend'),
    "class" => 'EmperorBestFriend',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-2 ${empire} OR -2 ${tribes}'),
    "description" => clienttranslate('At game end : IF this card is in the same party as the Buried Emperor
    <br>
    It counts as 2 ${visible} for the winning faction'),
    "token_action" => [1=> -2, 2 => -2, 3 => TOKEN_OR],
    "isYellow" => 1
  ],
  QueenOfTheWild => [
    "name" => clienttranslate('Queen Of The Wild'),
    "class" => 'QueenOfTheWild',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-2 ${empire} OR +2 ${tribes}'),
    "description" => clienttranslate('At game end : IF this card is in the party of Myrad ${undead} ${tribes} or Lemron ${water_folk} ${tribes}
    <br>
    It counts as 2 ${visible} for the winning faction'),
    "token_action" => [1=> -2, 2 => 2, 3 => TOKEN_OR],
    "isYellow" => 1
  ],
  WellFundedQueen => [
    "name" => clienttranslate('Well-Funded Queen'),
    "class" => 'WellFundedQueen',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+2 ${empire} OR -2 ${tribes}'),
    "description" => clienttranslate('At game end : IF this card is in the party of Enned ${undead} ${water_folk} or Cyra ${empire} ${water_folk}
    <br>
    It counts as 2 ${visible} for the winning faction'),
    "token_action" => [1=> 2, 2 => -2, 3 => TOKEN_OR],
    "isYellow" => 1
  ],
  QueenOfTheStreets => [
    "name" => clienttranslate('Queen Of The Streets'),
    "class" => 'QueenOfTheStreets',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+2 ${empire} OR +2 ${tribes}'),
    "description" => clienttranslate('At game end : IF this card is in the party of Pavyr ${empire} ${tribes} or Xiadul ${undead} ${empire}
    <br>
    It counts as 2 ${visible} for the winning faction'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_OR],
    "isYellow" => 1
  ],
];

$this->FL_CARDS = [
  HERO_91 => [
    "name" => clienttranslate('Obliging Ogre'),
    "class" => 'ObligingOgre',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-4 ${empire} OR +4 ${tribes}'),
    "description" => clienttranslate('Turn over 2 of your ${visible}'),
    "token_action" => [1=> -4, 2 => +4, 3 => TOKEN_OR],
    "nb_cards_to_select" => 2,
    "isGuardian" => 1
  ],
  HERO_92 => [
    "name" => clienttranslate('Sleepy Hop-Goblin'),
    "class" => 'SleepyHopGoblin',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-2 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Take 1 ${hidden} from another ${player}
    <br>
    Place it in your party ${hidden}'),
    "token_action" => [1=> -2, 2 => +1, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_93 => [
    "name" => clienttranslate('Gigantic Duo'),
    "class" => 'GiganticDuo',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${tribes}'),
    "description" => clienttranslate('You may bury any 1 ${visible} ${empire}
    <br>
    AND/OR bury any 1 ${visible} ${water_folk}'),
    "token_action" => [1=> 0, 2 => -1, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "canPass" => 1
  ],
  HERO_94 => [
    "name" => clienttranslate('Surprised Sapling'),
    "class" => 'SurprisedSapling',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${empire}'),
    "description" => clienttranslate('Look at the hand of another ${player}
    <br>
    Take 1 of their cards. Place it into your party ${hidden}'),
    "token_action" => [1=> +1, 2 => 0, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_95 => [
    "name" => clienttranslate('Fungified Troll'),
    "class" => 'FungifiedTroll',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('-1 ${empire} AND/OR -1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 of your ${visible} with 1 ${undead} ${visible} OR 1 ${empire} ${visible} of another ${player}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND_OR],
    "isGuardian" => 1
  ],
  HERO_96 => [
    "name" => clienttranslate('Twiggy Tree Keeper'),
    "class" => 'TwiggyTreeKeeper',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+2 ${empire} OR +2 ${tribes}'),
    "description" => clienttranslate('Look at any 2 ${hidden}
    <br>
    Then bury any 1 ${hidden}'),
    "token_action" => [1=> +2, 2 => +2, 3 => TOKEN_OR],
    "isGuardian" => 1,
  ],
  HERO_97 => [
    "name" => clienttranslate('Neckless Charlatan'),
    "class" => 'NecklessCharlatan',
    "faction_id" => TRIBES,
    "description_token" => clienttranslate('+1 ${tribes} OR +2 ${tribes}'),
    "description" => clienttranslate('Look at any 1 ${corruption} AND/OR perform the ability of any 1 ${artifact} as if it was your ${artifact}')
  ],

  HERO_98 => [
    "name" => clienttranslate('Patient Protector'),
    "class" => 'PatientProtector',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} OR -2 ${tribes}'),
    "description" => clienttranslate('Exchange 2 cards from ${tavern} with 2 of your ${visible}'),
    "token_action" => [1=> +1, 2 => -2, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 2
  ],
  HERO_99 => [
    "name" => clienttranslate('Misinformed Mechatron'),
    "class" => 'MisinformedMechatron',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${tribes}'),
    "description" => clienttranslate('Place up to 2 cards from your hand into your party ${hidden}'),
    "token_action" => [1=> 0, 2 => +1, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 2,
    "canPass" => 1
  ],
  HERO_100 => [
    "name" => clienttranslate('Mended Colossus'),
    "class" => 'MendedColossus',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-1 ${empire}'),
    "description" => clienttranslate('Bury 1 ${visible} from the next AND previous ${player}'),
    "token_action" => [1=> -1, 2 => 0, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 2
  ],
  HERO_101 => [
    "name" => clienttranslate('Huggable Hulk'),
    "class" => 'HuggableHulk',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+4 ${empire} OR -4 ${tribes}'),
    "description" => clienttranslate('Bury 1 of your ${visible} that is not ${guardian}'),
    "token_action" => [1=> +4, 2 => -4, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_102 => [
    "name" => clienttranslate('Valuable Vindicator'),
    "class" => 'ValuableVindicator',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} AND/OR +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 of your ${visible} with 1 ${visible} ${water_folk} OR ${visible} ${tribes} of another ${player}'),
    "token_action" => [1=> +1, 2 => +1, 3 => TOKEN_AND_OR],
    "isGuardian" => 1
  ],
  HERO_103 => [
    "name" => clienttranslate('Graceful Griffin'),
    "class" => 'GracefulGriffin',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('-2 ${empire} OR -2 ${tribes}'),
    "description" => clienttranslate('Exchange up to 2 cards of your ${hidden} with cards from your hand.'),
    "token_action" => [1=> -2, 2 => -2, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 2,
    "canPass" => 1
  ],
  HERO_104 => [
    "name" => clienttranslate('Self-Made Widow'),
    "class" => 'SelfMadeWidow',
    "faction_id" => EMPIRE,
    "description_token" => clienttranslate('+1 ${empire} OR +2 ${empire}'),
    "description" => clienttranslate('Exchange any 2 ${corruption} OR any 2 ${artifact}
    <br>
    Every ${player} keeps their ${artifact} tokens.')
  ],

  HERO_105 => [
    "name" => clienttranslate('Severed Seraph'),
    "class" => 'SeveredSeraph',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Exchange 1 of your ${visible} ${guardian} with 1 ${visible} ${guardian} of another ${player}'),
    "token_action" => [1=> +1, 2 => +1, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_106 => [
    "name" => clienttranslate('Baneful Beacon'),
    "class" => 'BanefulBeacon',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} OR -1 ${tribes}'),
    "description" => clienttranslate('You may bury up to any 2 ${visible} ${guardian}'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 2,
    "canPass" => 1
  ],
  HERO_107 => [
    "name" => clienttranslate('Demonic Darter'),
    "class" => 'DemonicDarter',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} OR +2 ${tribes}'),
    "description" => clienttranslate('Take up to 3 ${guardian} from ${graveyard} into your hand.'),
    "token_action" => [1=> 2, 2 => 2, 3 => TOKEN_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 3,
    "canPass" => 1
  ],
  HERO_108 => [
    "name" => clienttranslate('Possessed Poodle'),
    "class" => 'PossessedPoodle',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('-1 ${empire} AND -1 ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}
    <br>
    They have to skip their next turn.'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND],
    "isGuardian" => 1
  ],
  HERO_109 => [
    "name" => clienttranslate('Philantropic Phantom'),
    "class" => 'PhilantropicPhantom',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('All other ${player} have to bury 1 of their ${visible} OR ${hidden}'),
    "token_action" => [1=> +1, 2 => +1, 3 => TOKEN_AND],
    "isGuardian" => 1
  ],
  HERO_110 => [
    "name" => clienttranslate('Obnoxious Nightmare'),
    "class" => 'ObnoxiousNightmare',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Place any 1 card from ${graveyard} into your party ${hidden}'),
    "token_action" => [1=> +1, 2 => +1, 3 => TOKEN_AND],
    "isGuardian" => 1
  ],
  HERO_111 => [
    "name" => clienttranslate('Open-Minded Mentalist'),
    "class" => 'OpenMindedMentalist',
    "faction_id" => UNDEAD,
    "description_token" => clienttranslate('+2 ${empire} AND/OR +1 ${tribes}'),
    "description" => clienttranslate('Draw 1 ${corruption} and place it on any 1 ${visible}
    <br>
    OR Take 1 ${artifact} token from the box and place it on any 1 ${artifact}'),
    "token_action" => [1=> +2, 2 => +1, 3 => TOKEN_AND_OR]
  ],

  HERO_112 => [
    "name" => clienttranslate('Oppressed Ocean'),
    "class" => 'OppressedOcean',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-X ${empire} AND/OR -X ${tribes}'),
    "description" => clienttranslate('Pick 1 ${player}. X is the number of ${visible} {guardian} in their party (max -4)'),
    "isGuardian" => 1
  ],
  HERO_113 => [
    "name" => clienttranslate('Hard-Shelled Titan'),
    "class" => 'HardShelledTitan',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('All other ${player} have to give you 1 card from their hand. Place 1 of them into your party ${hidden}. Discard the others.'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_114 => [
    "name" => clienttranslate('Abysmal Automaton'),
    "class" => 'AbysmalAutomaton',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-1 ${empire} AND/OR -1 ${tribes}'),
    "description" => clienttranslate('Look at the top 5 cards from ${harbor}
    <br>
    Take up to 3 of them into your hand. Discard the others.'),
    "token_action" => [1=> -1, 2 => -1, 3 => TOKEN_AND_OR],
    "isGuardian" => 1,
    "nb_cards_to_select" => 3,
    "canPass" => 1
  ],
  HERO_115 => [
    "name" => clienttranslate('Crabby Knight'),
    "class" => 'CrabbyKnight',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} AND +1 ${tribes}'),
    "description" => clienttranslate('Turn over 1 ${visible} OR ${hidden} of every other ${player}'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_AND],
    "isGuardian" => 1
  ],
  HERO_116 => [
    "name" => clienttranslate('Snappy Sea Snake'),
    "class" => 'SnappySeaSnake',
    "faction_id" => WATER_FOLK,
    "description" => clienttranslate('Move the ${tribes} OR ${empire} marker 1 OR 2 steps in any direction.'),
    "isGuardian" => 1
  ],
  HERO_117 => [
    "name" => clienttranslate('ShellFish Defender'),
    "class" => 'ShellFishDefender',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('+1 ${empire} OR +1 ${tribes}'),
    "description" => clienttranslate('Look at the top 4 cards from ${wilderness}
    <br>
    Place 1 of them into your party ${hidden}
    <br>
    AND Take 1 of them into your hand.'),
    "token_action" => [1=> 1, 2 => 1, 3 => TOKEN_OR],
    "isGuardian" => 1
  ],
  HERO_118 => [
    "name" => clienttranslate('Piranha Priestess'),
    "class" => 'PiranhaPriestess',
    "faction_id" => WATER_FOLK,
    "description_token" => clienttranslate('-2 ${empire} AND/OR -1 ${tribes}'),
    "description" => clienttranslate('Discard any 1 ${artifact} token OR look at any 1 ${corruption}. You may discard it.'),
    "token_action" => [1=> -2, 2 => -1, 3 => TOKEN_AND_OR]
  ],

  MotherOfGuardians => [
    "name" => clienttranslate('Mother Of Guardians'),
    "class" => 'MotherOfGuardians',
    "faction_id" => GUARDIAN,
    "description_token" => clienttranslate('-2 ${empire} AND/OR -2 ${tribes}'),
    "description" => clienttranslate('At game end : This card counts as +1 ${visible} for the winning faction for every 2 ${guardian} in your party'),
    "token_action" => [1=> -2, 2 => -2, 3 => TOKEN_AND_OR],
    "isYellow" => 1,
    "isGuardian" => 1
  ],
  // UnmarkedMountain => [
  //   "name" => clienttranslate('Unmarked Mountain'),
  //   "class" => 'UnmarkedMountain',
  //   "faction_id" => GUARDIAN,
  //   "description" => '',
  //   "isYellow" => 1
  //   "isGuardian" => 1
  // ],

];

$this->CARDS_DATA = $this->HERO_CARDS + $this->QUEENS_CARDS + $this->FL_CARDS;