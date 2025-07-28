<?php
// Game options
define('OPTION_QUEENS', 110);
define('OPTION_FORGOTTEN_LEGENDS', 120);
define('OPTION_CORRUPTION', 121);
define('OPTION_ARTIFACTS', 122);

// Card Type
define('LEADER', 1);
define('HERO', 2);

// Constants
define('EMPIRE_TOKEN',10);
define('TRIBES_TOKEN',11);
define('CARD_PLAYED',12);
define('EFFECT_STEP',14);
define('NB_DISCARDED_CARD',15);
define('SELECTED_CARD',16);
define('SELECTED_CARD_2',17);
define('CURRENT_PLAYER',18);
define('SELECTED_PLAYER',19);
define('SELECTED_FACTION',20);
define('FIRST_PLAYER',21);
//FORGOTTEN LEGENDS
define('GUARDIAN_TOKEN',31);
define('DRAW_FATE',32);
define('SKIP_PLAYER',33);
define('ARTIFACT_PLAYED',34);
define('PREVIOUS_STATE',35);

// Token choice
define('TOKEN_AND', 'and');
define('TOKEN_OR', 'or');
define('TOKEN_AND_OR', 'and_or');
define('TOKEN_NONE', 'none');

// Faction
define('UNDEAD', 1);
define('WATER_FOLK', 2);
define('EMPIRE', 3);
define('TRIBES', 4);
define('EMPEROR', 10);
//FORGOTTEN LEGENDS
define('GUARDIAN', 5);

// Location
define('GRAVEYARD', 'graveyard');
define('TAVERN', 'tavern');
define('HARBOR', 'harbor');
define('WILDERNESS', 'wilderness');
define('VISIBLE', 'visible');
define('HIDDEN', 'hidden');
define('PLAYER', 'player');
define('HAND', 'hand');
// define('GRAVEYARD', 1);
// define('TAVERN', 2);
// define('HARBOR', 3);
// define('WILDERNESS', 4);
// define('VISIBLE', 5);
// define('HIDDEN', 6);
// define('PLAYER', 7);
// define('HAND', 10);

//FORGOTTEN LEGENDS
define('CORRUPTION', 'corruption');
define('ARTIFACT', 'artifact');
define('SLOW', 'slow');
define('FAST', 'fast');
define('ARTIFACTTOKEN', 'artifactToken');

// State constants
define('ST_BGA_GAME_SETUP', 1);

define('ST_PRIVATE_SETUP', 10);
define('ST_PLAYER_SETUP', 11);
define('ST_SETUP_TRANSITION', 12);

define('ST_PLAYER_ACTION', 20);
define('ST_PLAYER_DRAW_CARD', 22);
define('ST_PLAYER_DISCARD', 23);

define('ST_PLAYER_CARD_EFFECT_MANAGER', 30);

define('ST_NEXT_PLAYER', 40);

define('ST_TRIGGER_END', 97);
define('ST_END_SCORE', 98);
define('ST_END_GAME', 99);

define('ST_GAME_CHANGE_ACTIVE_PLAYER', 52);
define('ST_PLAYER_CARD_EFFECT_WILLBENDINGWITCH', 53);
define('ST_PLAYER_CARD_EFFECT_PHILANTROPICPHANTOM', 54);
define('ST_PLAYER_CARD_EFFECT_HARDSHELLEDTITAN', 55);

define('ST_PLAYER_FATE', 60);

define('ST_PLAYER_SPREAD_CORRUPTION', 61);
define('ST_NEXT_PLAYER_CORRUPTION', 62);

define('ST_PLAYER_ARTIFACT_SETUP', 63);
define('ST_PLAYER_ARTIFACT', 64);
define('ST_PLAYER_ARTIFACT_EFFECT_MANAGER', 65);

// Leaders
define('LEMRON', 1);
define('CYRA', 2);
define('MYRAD', 3);
define('XIADUL', 4);
define('PAVYR', 5);
define('ENNED', 6);
//FORGOTTEN LEGENDS
define('TISSA', 7);
define('ULC', 8);
define('IRAFEL', 9);
define('VYMA', 10);

// Heroes
define('HERO_1', 1);
define('HERO_2', 2);
define('HERO_3', 3);
define('HERO_4', 4);
define('HERO_5', 5);
define('HERO_6', 6);
define('HERO_7', 7);
define('HERO_8', 8);
define('HERO_9', 9);
define('HERO_10', 10);
define('HERO_11', 11);
define('HERO_12', 12);
define('HERO_13', 13);
define('HERO_14', 14);
define('HERO_15', 15);
define('HERO_16', 16);
define('HERO_17', 17);
define('HERO_18', 18);
define('HERO_19', 19);
define('HERO_20', 20);
define('HERO_21', 21);
define('HERO_22', 22);
define('HERO_23', 23);
define('HERO_24', 24);
define('HERO_25', 25);
define('HERO_26', 26);
define('HERO_27', 27);
define('HERO_28', 28);
define('HERO_29', 29);
define('HERO_30', 30);
define('HERO_31', 31);
define('HERO_32', 32);
define('HERO_33', 33);
define('HERO_34', 34);
define('HERO_35', 35);
define('HERO_36', 36);
define('HERO_37', 37);
define('HERO_38', 38);
define('HERO_39', 39);
define('HERO_40', 40);
define('HERO_41', 41);
define('HERO_42', 42);
define('HERO_43', 43);
define('HERO_44', 44);
define('HERO_45', 45);
define('HERO_46', 46);
define('HERO_47', 47);
define('HERO_48', 48);
define('HERO_49', 49);
define('HERO_50', 50);
define('HERO_51', 51);
define('HERO_52', 52);
define('HERO_53', 53);
define('HERO_54', 54);
define('HERO_55', 55);
define('HERO_56', 56);
define('HERO_57', 57);
define('HERO_58', 58);
define('HERO_59', 59);
define('HERO_60', 60);
define('HERO_61', 61);
define('HERO_62', 62);
define('HERO_63', 63);
define('HERO_64', 64);
define('HERO_65', 65);
define('HERO_66', 66);
define('HERO_67', 67);
define('HERO_68', 68);
define('HERO_69', 69);
define('HERO_70', 70);
define('HERO_71', 71);
define('HERO_72', 72);
define('HERO_EMPEROR', 73);

//KICKSTARTER
define('HERO_73', 74);
define('HERO_74', 75);
define('HERO_75', 76);
define('HERO_76', 77);
define('HERO_77', 78);
define('HERO_78', 79);
define('HERO_79', 80);
define('HERO_80', 81);
define('HERO_81', 82);
define('HERO_82', 83);
define('HERO_83', 84);
define('HERO_84', 85);
define('EmperorBestFriend', 86);
define('QueenOfTheWild', 87);
define('WellFundedQueen', 88);
define('QueenOfTheStreets', 89);
define('KeeperOfDiscord', 90);

//FORGOTTEN LEGENDS
define('HERO_91', 91);
define('HERO_92', 92);
define('HERO_93', 93);
define('HERO_94', 94);
define('HERO_95', 95);
define('HERO_96', 96);
define('HERO_97', 97);
define('HERO_98', 98);
define('HERO_99', 99);
define('HERO_100', 100);
define('HERO_101', 101);
define('HERO_102', 102);
define('HERO_103', 103);
define('HERO_104', 104);
define('HERO_105', 105);
define('HERO_106', 106);
define('HERO_107', 107);
define('HERO_108', 108);
define('HERO_109', 109);
define('HERO_110', 110);
define('HERO_111', 111);
define('HERO_112', 112);
define('HERO_113', 113);
define('HERO_114', 114);
define('HERO_115', 115);
define('HERO_116', 116);
define('HERO_117', 117);
define('HERO_118', 118);
define('MotherOfGuardians', 119);
define('UnmarkedMountain', 120);
?>
