<?php

class Notifications {
    static function playCard(int $player_id, $card) {
        $card = self::getCardInfos($card);
        self::notifyAll('playCard', clienttranslate('${player_name} plays ${card_name} ${to}'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'card' => $card,
            'card_name' => $card->name,
            'to' => VISIBLE
        ]);

        switch($card->type) {
            case UNDEAD:
                self::incStat( 1, "playedUndead", $player_id );
            break;
            case WATER_FOLK:
                self::incStat( 1, "playedWaterfolk", $player_id );
            break;
            case EMPIRE:
                self::incStat( 1, "playedEmpire", $player_id );
            break;
            case TRIBES:
                self::incStat( 1, "playedTribes", $player_id );
            break;
        }
        if($card->isGuardian) self::incStat( 1, "playedGuardian", $player_id );
    }
    
    static function drawCard(int $player_id, $card, $from, $fromString, $cardInPick = false) {
        //$fromString = HiddenLeaders::get()->LOCATIONS[$from];
            
        $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop($fromString)) : HiddenLeaders::get()->getTopCard($fromString);
        $remainingCards = HiddenLeaders::get()->getRemainingCards($fromString);
        
        self::notify($player_id, 'drawCard', '', [
            'playerId' => $player_id,
            'card' => self::getCardInfos($card),
            'topCard' => $topCard,
            'remainingCards' => $remainingCards,
            'from_value' => $from,
            'flagNotif' => true,
            'cardInPick' => $cardInPick
        ]);

        if($from == GRAVEYARD && !$cardInPick) {
            self::notifyAll('drawCard', '', [
                'playerId' => $player_id,
                'card' => self::getCardInfos($card),
                'topCard' => $topCard,
                'remainingCards' => $remainingCards,
                'from_value' => $from,
                'cardInPick' => $cardInPick
            ]);
        }
        else {
            self::notifyAll('drawCard', '', [
                'playerId' => $player_id,
                'card' => HeroCard::onlyId($card),
                'topCard' => $topCard,
                'remainingCards' => $remainingCards,
                'from_value' => $from,
                'cardInPick' => $cardInPick
            ]);
        }  
    }
    static function drawFate(int $player_id, $card) {
        $topCard = HiddenLeaders::get()->getTopFateCard();
        $remainingCards = HiddenLeaders::get()->getRemainingCards('fate');

        self::notify($player_id, 'drawFate', '', [
            'playerId' => $player_id,
            'card' => $card,
            'topCard' => $topCard,
            'remainingCards' => $remainingCards
        ]);
    }

    static function drawCardNotif(int $player_id, $cards, $from, $cardInPick = false) {
        $logs = [];
        $args = [];
        $i = 0;
        $card_names = [];

        foreach($cards as $cardId => $card) {
            array_push($card_names, self::getCardInfos($card)->name);
            
            $logs[] = '${card_names'. $i .'}';
            $args['card_names' . $i] = self::getCardInfos($card)->name; // constants holding clienttranslated names of resources
            $args['i18n'][] = 'card_names' . $i;
            $i++;
        }

        self::notify($player_id, 'drawCardNotif', clienttranslate('You draw ${card_names} from ${from}'), [
            'playerId' => $player_id,
            'cards' => self::getCardsInfos($cards),
            'card_names' => $card_names,
            // 'card_names' => [
            //     'log' => implode(', ',$logs),
            //     'args' => $args
            // ],
            'from' => $from
        ]);

        if($from == GRAVEYARD && !$cardInPick) {
            self::notifyAll('drawCardNotif', clienttranslate('${player_name} draws ${card_names} from ${from}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'cards' => self::getCardsInfos($cards),
                'card_names' => $card_names,
                // 'card_names' => [
                //     'log' => implode(', ',$logs),
                //     'args' => $args
                // ],
                'from' => $from
            ]);
        }
        else {
            self::notifyAll('drawCardNotif', clienttranslate('${player_name} draws ${nbCards} card(s) from ${from}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'cards' => HeroCard::onlyIds($cards),
                'nbCards' => count($cards),
                'from' => $from
            ]);
        }   
    }

    // static function drawCard(int $player_id, $cards, $from) {
    //     $card_names = [];
    //     foreach($cards as $cardId => $card) {
    //         array_push($card_names, self::getCardInfos($card)->name);
    //     }
    //     $logs = [];
    //     $args = [];
    //     $i = 0;
    //     foreach($card_names as $card_name){
    //         $logs[] = '${card_names'. $i .'}';
    //         $args['card_names' . $i] = $card_name; // constants holding clienttranslated names of resources
    //         $args['i18n'][] = 'card_names' . $i;
    //         $i++;
    //     }

    //     $fromString = HiddenLeaders::get()->LOCATIONS[$from];

    //     $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop($fromString)) : HiddenLeaders::get()->getTopCard($fromString);
    //     $remainingCards = HiddenLeaders::get()->getRemainingCards($fromString);

    //     // foreach([HARBOR => 'deck', WILDERNESS => 'discard', GRAVEYARD => 'graveyard'] as $key => $value) {

    //     //     if($from == $key) {
    //     //         $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop($value)) : HiddenLeaders::get()->getTopCard($value);
    //     //         $remainingCards = HiddenLeaders::get()->getRemainingCards($value);
    //     //     }
    //     // }

    //     self::notify($player_id, 'drawCard', clienttranslate('You draw ${card_names} from ${from}'), [
    //         'playerId' => $player_id,
    //         'cards' => self::getCardsInfos($cards),
    //         'card_names' => [
    //             'log' => $card_names,
    //             'args' => $args
    //         ],
    //         'topCard' => $topCard,
    //         'remainingCards' => $remainingCards,
    //         'from' => $from,
    //         'from_value' => $from
    //     ]);

    //     if($from == GRAVEYARD) {
    //         self::notifyAll('drawCard', clienttranslate('${player_name} draws ${card_names} from ${from}'), [
    //             'playerId' => $player_id,
    //             'player_name' => self::getPlayerName($player_id),
    //             'cards' => self::getCardsInfos($cards),
    //             'card_names' => [
    //                 'log' => $card_names,
    //                 'args' => $args
    //             ],
    //             'topCard' => $topCard,
    //             'remainingCards' => $remainingCards,
    //             'from' => $from,
    //             'from_value' => $from
    //         ]);
    //     }
    //     else {
    //         self::notifyAll('drawCard', clienttranslate('${player_name} draws ${nbCards} card(s) from ${from}'), [
    //             'playerId' => $player_id,
    //             'player_name' => self::getPlayerName($player_id),
    //             'cards' => HeroCard::onlyIds($cards),
    //             'nbCards' => count($cards),
    //             'topCard' => $topCard,
    //             'remainingCards' => $remainingCards,
    //             'from' => $from,
    //             'from_value' => $from
    //         ]);
    //     }   
    // }

    static function drawCardFromTavern(int $player_id, $card) {
        self::notifyAll('drawCardFromTavern', clienttranslate('${player_name} takes ${card_name} from ${from}'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'card' => $card,
            'card_name' => $card->name,
            'from' => TAVERN
        ]);
    }

    static function fillTavern($card, $from) {

        $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop(HiddenLeaders::get()->LOCATIONS[$from])) : HiddenLeaders::get()->getTopCard(HiddenLeaders::get()->LOCATIONS[$from]);
        $remainingCards = HiddenLeaders::get()->getRemainingCards(HiddenLeaders::get()->LOCATIONS[$from]);

        self::notifyAll('fillTavern', clienttranslate('Fill up ${to} with ${card_name} from ${from}'), [
            'card' => $card,
            'card_name' => $card->name,
            'to' => TAVERN,
            'from' => $from,
            'from_value' => $from,
            'topCard' => $topCard,
            'remainingCards' => $remainingCards
        ]);
    }

    static function moveToken(int $player_id, int $empire_pos, int $tribes_pos) {
        $previous_empire_pos = HiddenLeaders::get()->getGameStateValue(EMPIRE_TOKEN);
        $previous_tribes_pos = HiddenLeaders::get()->getGameStateValue(TRIBES_TOKEN);

        if($empire_pos == $previous_empire_pos && $tribes_pos == $previous_tribes_pos) {
            $description = clienttranslate('No token movement');
        }
        else {
            $description = clienttranslate('${player_name} moves ${empire_token} from position ${previous_empire_pos} to ${empire_pos} and ${tribes_token} from position ${previous_tribes_pos} to ${tribes_pos}');

            if($empire_pos == $previous_empire_pos) {
                $description = clienttranslate('${player_name} moves ${tribes_token} from position ${previous_tribes_pos} to ${tribes_pos}');
            }
            else if($tribes_pos == $previous_tribes_pos) {
                $description = clienttranslate('${player_name} moves ${empire_token} from position ${previous_empire_pos} to ${empire_pos}');
            }
        }
        
        HiddenLeaders::get()->setGameStateValue( EMPIRE_TOKEN, $empire_pos );
        HiddenLeaders::get()->setGameStateValue( TRIBES_TOKEN, $tribes_pos );

        $winningFaction = HiddenLeaders::get()->getWinningFaction();

        self::notifyAll('moveToken', $description, [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'empire_token' => 'empire-token-log',
            'tribes_token' => 'tribes-token-log',
            'empire_pos' => $empire_pos,
            'tribes_pos' => $tribes_pos,
            'winningFaction_translated' => HiddenLeaders::get()->FACTIONS[$winningFaction],
            'winningFaction' => $winningFaction,
            'winningFaction_value' => $winningFaction,
            'previous_empire_pos' => $previous_empire_pos,
            'previous_tribes_pos' => $previous_tribes_pos
        ]);

        self::incStat( $empire_pos - $previous_empire_pos, "moveEmpireToken", $player_id );
        self::incStat( $tribes_pos - $previous_tribes_pos, "moveTribesToken", $player_id );
    }

    static function moveGuardianToken(int $player_id, int $guardian_pos) {
        $previous_guardian_pos = HiddenLeaders::get()->getGameStateValue(GUARDIAN_TOKEN);

        if($guardian_pos == $previous_guardian_pos) {
            $description = clienttranslate('No token movement');
        }
        else {
            $description = clienttranslate('${player_name} moves ${guardian_token} from position ${previous_guardian_pos} to ${guardian_pos}');
        }
        
        HiddenLeaders::get()->setGameStateValue( GUARDIAN_TOKEN, $guardian_pos );

        $winningFaction = HiddenLeaders::get()->getWinningFaction();

        self::notifyAll('moveToken', $description, [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'guardian_token' => 'guardian-token-log',
            'guardian_pos' => $guardian_pos,
            'winningFaction_translated' => HiddenLeaders::get()->FACTIONS[$winningFaction],
            'winningFaction' => $winningFaction,
            'winningFaction_value' => $winningFaction,
            'previous_guardian_pos' => $previous_guardian_pos
        ]);

        self::incStat( $guardian_pos - $previous_guardian_pos, "moveGuardianToken", $player_id );
    }

    static function discard(int $player_id, $cards, bool $anonymize, $from) {
        $card_names = [];
        foreach($cards as $cardId => $card) {
            array_push($card_names, self::getCardInfos($card)->name);
        }
        $logs = [];
        $args = [];
        $i = 0;
        foreach($card_names as $card_name){
            $logs[] = '${card_names'. $i .'}';
            $args['card_names' . $i] = $card_name; // constants holding clienttranslated names of resources
            $args['i18n'][] = 'card_names' . $i;
            $i++;
        }

        if($anonymize) {
            self::notify($player_id, 'discard', clienttranslate('You discard ${card_names} to ${to}'), [
                'playerId' => $player_id,
                'cards' => self::getCardsInfos($cards),
                'card_names' => $card_names,
                // 'card_names' => [
                //     'log' => implode(', ',$logs),
                //     'args' => $args
                // ],
                'topCard' => HiddenLeaders::get()->getTopCard('discard'),
                'remainingCards' => HiddenLeaders::get()->getRemainingCards('discard'),
                'from_value' => $from,
                'to' => WILDERNESS
            ]);

            self::notifyAll('discard', clienttranslate('${player_name} discards ${nbCards} card(s) to ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'cards' => HeroCard::onlyIds(array_values($cards)),
                'nbCards' => count($cards),
                'topCard' => HiddenLeaders::get()->getTopCard('discard'),
                'remainingCards' => HiddenLeaders::get()->getRemainingCards('discard'),
                'from_value' => $from,
                'to' => WILDERNESS
            ]);
        }
        else {
            self::notifyAll('discard', clienttranslate('${player_name} discards ${card_names} to ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'cards' => self::getCardsInfos($cards),
                'card_names' => $card_names,
                // 'card_names' => [
                //     'log' => $card_names,
                //     'args' => $args
                // ],
                'topCard' => HiddenLeaders::get()->getTopCard('discard'),
                'remainingCards' => HiddenLeaders::get()->getRemainingCards('discard'),
                'from_value' => $from,
                'to' => WILDERNESS
            ]);
        }
        
        self::incStat( count($cards), "discardedCards", $player_id );
    }

    static function place(int $player_id, $card, string $from, string $to, bool $anonymize, int $target_id = null) {
        if(!isset($card->name)) $card = self::getCardInfos($card);

        $topCard = $remainingCards = null;
        foreach([HARBOR, WILDERNESS, GRAVEYARD] as $value) {
            if($from == $value) {
                $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop(HiddenLeaders::get()->LOCATIONS[$value])) : HiddenLeaders::get()->getTopCard(HiddenLeaders::get()->LOCATIONS[$value]);
                $remainingCards = HiddenLeaders::get()->getRemainingCards(HiddenLeaders::get()->LOCATIONS[$value]);
            }
        }
        
        $target_name = '';
        
        if($target_id) {
            $target_name = self::getPlayerName($target_id);
        }

        if($anonymize) {
            self::notify($player_id, 'place', clienttranslate('You place ${card_name} from ${player_name2} ${from} into ${to}'), [
                'playerId' => $player_id,
                'targetId' => $target_id,
                'player_name2' => $target_name,
                'card' => $card,
                'card_name' => $card->name,
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to,
                'topCard' => $topCard,
                'remainingCards' => $remainingCards
            ]);
            
            
            self::notifyAll('place', clienttranslate('${player_name} places a card from ${player_name2} ${from} into ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => $target_name,
                'card' => HeroCard::anonymize($card),
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to,
                'topCard' => $topCard,
                'remainingCards' => $remainingCards
            ]);
        }
        else {
            self::notifyAll('place', clienttranslate('${player_name} places ${card_name} from ${player_name2} ${from} into ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => $target_name,
                'card' => $card,
                'card_name' => $card->name,
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to,
                'topCard' => $topCard,
                'remainingCards' => $remainingCards
            ]);
        }
    }

    static function pick(int $player_id, int $target_id, $card, $from, $to, $anonymize) {
        $card = self::getCardInfos($card);

        $endDescription = $from == HAND && $to == HAND ? '' : '<br>${from} => ${to}';
        
        if($anonymize) {
            
            self::notify($player_id, 'pick', clienttranslate('You take ${card_name} from ${player_name}'), [
                'playerId' => $player_id,
                'targetId' => $target_id,
                'player_name' => self::getPlayerName($target_id),
                'card' => $card,
                'card_name' => $card->name,
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to
            ]);

            self::notify($target_id, 'pick', clienttranslate('${player_name} takes ${card_name} from you'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'card' => $card,
                'card_name' => $card->name,
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to
            ]);

            self::notifyAll('pick', clienttranslate('${player_name} takes a card from ${player_name2}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => self::getPlayerName($target_id),
                'card' => HeroCard::anonymize($card),
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to
            ]);
        }
        else {
            self::notifyAll('pick', clienttranslate('${player_name} takes ${card_name} from ${player_name2}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => self::getPlayerName($target_id),
                'card' => $card,
                'card_name' => $card->name,
                'from' => $from,
                'to' => $to,
                'from_value' => $from,
                'to_value' => $to
            ]);
        }
    }

    static function bury(int $player_id, int $target_id = null, $card, $from, bool $anonymize) {
        if(!isset($card->name)) $card = self::getCardInfos($card);
        
        if($card->isGuardian) HiddenLeaders::get()->setGameStateValue( DRAW_FATE, 1 );

        $target_name = '';
        
        if($target_id && $target_id != $player_id) {
            $target_name = self::getPlayerName($target_id);
            $description = clienttranslate('${player_name} buries ${card_name} from ${player_name2}');
        }
        else $description = clienttranslate('${player_name} buries ${card_name} from ${player_name}');

        self::notifyAll('bury', $description, [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'targetId' => $target_id,
            'player_name2' => $target_name,
            'card' => $card,
            'card_name' => $card->name,
            'from' => $from,
            'from_value' => $from,
            'to' => GRAVEYARD,
            'topCard' => HiddenLeaders::get()->getTopCard('graveyard'),
            'remainingCards' => HiddenLeaders::get()->getRemainingCards('graveyard'),
        ]);

        self::incStat( 1, "buriedCards", $player_id );
    }

    static function flip(int $player_id, int $target_id = null, $card, $flip_from, $flip_to) {
        $card = self::getCardInfos($card);
        $target_name = '';
        
        if($target_id && $target_id != $player_id) {
            $target_name = self::getPlayerName($target_id);
            $description = clienttranslate('${player_name} turns ${to} ${card_name} from ${player_name2}');
        }
        else $description = clienttranslate('${player_name} turns ${to} ${card_name} from ${player_name}');

        self::notifyAll('flip', $description, [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'targetId' => $target_id,
            'player_name2' => $target_name,
            'card' => $card,
            'card_name' => $card->name,
            'from' => $flip_from,
            'from_value' => $flip_from,
            'to' => $flip_to
        ]);
    }

    static function exchange(int $player_id, $target_id, $card_1, $card_2, string $card_1_location, string $card_2_location, bool $anonymize, $cardClass = null) {
        $card_1 = self::getCardInfos($card_1);
        $card_2 = self::getCardInfos($card_2);
        
        // $target_name = '';
            
        // if($target_id && $target_id != $player_id) {
        //     $target_name = self::getPlayerName($target_id);
        // }

        $from_player_name = '';
        if(in_array($card_1_location, [VISIBLE,HIDDEN,HAND])) $from_player_name = self::getPlayerName($player_id);
        
        $to_player_name = '';
        if(in_array($card_2_location, [VISIBLE,HIDDEN,HAND])) $to_player_name = self::getPlayerName($target_id);
        
        if($cardClass == 'CarelessCartographer') [$from_player_name, $to_player_name] = [$to_player_name, $from_player_name];
        
        if($anonymize) {
            self::notify($player_id, 'exchange', clienttranslate('You exchange ${card_1_name} from ${player_name2} ${from} <=> ${card_2_name} from ${player_name3} ${to}'), [
                'playerId' => $player_id,
                'targetId' => $target_id,
                'player_name2' => $from_player_name,
                'player_name3' => $to_player_name,
                'card_1' => $card_1,
                'card_2' => $card_2,
                'card_1_name' => $card_1->name,
                'card_2_name' => $card_2->name,
                'from' => $card_1_location,
                'to' => $card_2_location,
                'card_1_location_value' => $card_1_location,
                'card_2_location_value' => $card_2_location
            ]);
            
            if($target_id != null && $target_id != $player_id) {
                self::notify($target_id, 'exchange', clienttranslate('${player_name} exchanges ${card_1_name} from ${player_name2} ${from} <=> ${card_2_name} from ${player_name3} ${to}'), [
                    'playerId' => $player_id,
                    'player_name' => self::getPlayerName($player_id),
                    'targetId' => $target_id,
                    'player_name2' => $from_player_name,
                    'player_name3' => $to_player_name,
                    'card_1' => $card_1,
                    'card_2' => $card_2,
                    'card_1_name' => $card_1->name,
                    'card_2_name' => $card_2->name,
                    'from' => $card_1_location,
                    'to' => $card_2_location,
                    'card_1_location_value' => $card_1_location,
                    'card_2_location_value' => $card_2_location
                ]);
            }

            self::notifyAll('exchange', clienttranslate('${player_name} exchanges ${player_name2} ${from} <=> ${player_name3} ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => $from_player_name,
                'player_name3' => $to_player_name,
                'card_1' => HeroCard::anonymize($card_1),
                'card_2' => HeroCard::anonymize($card_2),
                'from' => $card_1_location,
                'to' => $card_2_location,
                'card_1_location_value' => $card_1_location,
                'card_2_location_value' => $card_2_location
            ]);

        }
        else {
            self::notifyAll('exchange', clienttranslate('${player_name} exchanges ${card_1_name} from ${player_name2} ${from} <=> ${card_2_name} from ${player_name3} ${to}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'targetId' => $target_id,
                'player_name2' => $from_player_name,
                'player_name3' => $to_player_name,
                'card_1' => $card_1,
                'card_2' => $card_2,
                'card_1_name' => $card_1->name,
                'card_2_name' => $card_2->name,
                'from' => $card_1_location,
                'to' => $card_2_location,
                'card_1_location_value' => $card_1_location,
                'card_2_location_value' => $card_2_location
            ]);
        }
    }

    static function look(int $player_id, $cards) {
        $cards = self::getCardsInfos($cards);

        $playersToNotify = [];
        $card_names = [];
        foreach($cards as $cardId => $card) {
            array_push($card_names, $card->name);

            $playersToNotify[$card->location_arg][] = $card;
        }
        $logs = [];
        $args = [];
        $i = 0;
        foreach($card_names as $card_name){
            $logs[] = '${card_names'. $i .'}';
            $args['card_names' . $i] = $card_name; // constants holding clienttranslated names of resources
            $args['i18n'][] = 'card_names' . $i;
            $i++;
        }

        self::notify($player_id, 'look', clienttranslate('${player_name} looks at ${card_names}'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'cards' => $cards,
            'card_names' => $card_names,
            // 'card_names' => [
            //     'log' => $card_names,
            //     'args' => $args
            // ],
        ]);

        foreach($playersToNotify as $playerToNotifyId => $cardsPlayerToNotify) {
            $card_names = [];
            foreach($cardsPlayerToNotify as $cardId => $card) {
                array_push($card_names, $card->name);
            }
            $logs = [];
            $args = [];
            $i = 0;
            foreach($card_names as $card_name){
                $logs[] = '${card_names'. $i .'}';
                $args['card_names' . $i] = $card_name; // constants holding clienttranslated names of resources
                $args['i18n'][] = 'card_names' . $i;
                $i++;
            }

            self::notify($playerToNotifyId, 'look', clienttranslate('${player_name} looks at your card(s) : ${card_names}'), [
                'playerId' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'cards' => $cards,
                'card_names' => $card_names,
                // 'card_names' => [
                //     'log' => $card_names,
                //     'args' => $args
                // ],
                //'titleChange' => '${actplayer} looks at your card(s)'
            ]); 
        }
    }

    static function noAction(int $player_id) {
        self::notifyAll('noAction', clienttranslate('${player_name} did no action'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
        ]);
    }

    static function reveal(int $player_id, $card, $from) {
        $card = self::getCardInfos($card);

        self::notifyAll('reveal', clienttranslate('${player_name} reveals ${card_name} from ${from}'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'card' => $card,
            'card_name' => $card->name,
            'from' => $from,
            'from_value' => $from
        ]);
    }

    static function cardInPlay(int $player_id, $card, $cardToMove) {
        $card = self::getCardInfos($card);

        self::notifyAll('cardInPlay', clienttranslate('${player_name} performs ${card_name} abilities'), [
            'playerId' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'card' => $card,
            'card_name' => $card->name,
            'cardToMove' => $cardToMove
        ]);
    }

    // static function cardInPick(int $player_id, $cards, $from) {
    //     foreach($cards as $cardId => $card) {

    //     }
    //     $topCard = $from == GRAVEYARD ? self::getCardInfos(HiddenLeaders::get()->cards->getCardOnTop(HiddenLeaders::get()->LOCATIONS[$from])) : HiddenLeaders::get()->getTopCard(HiddenLeaders::get()->LOCATIONS[$from]);
    //     $remainingCards = HiddenLeaders::get()->getRemainingCards(HiddenLeaders::get()->LOCATIONS[$from]);

    //     self::notify($player_id, 'cardInPick', clienttranslate('You pick ${nbCards} card(s) from ${from}'), [
    //         'playerId' => $player_id,
    //         'cards' => self::getCardsInfos($cards),
    //         'nbCards' => count($cards),
    //         'from' => $from,
    //         'from_value' => $from,
    //         'topCard' => $topCard,
    //         'remainingCards' => $remainingCards
    //     ]);

    //     self::notifyAll('cardInPick', clienttranslate('${player_name} picks ${nbCards} card(s) from ${from}'), [
    //         'playerId' => $player_id,
    //         'player_name' => self::getPlayerName($player_id),
    //         'cards' => HeroCard::onlyIds($cards),
    //         'nbCards' => count($cards),
    //         'from' => $from,
    //         'from_value' => $from,
    //         'topCard' => $topCard,
    //         'remainingCards' => $remainingCards
    //     ]);

        
    //     self::notify($player_id, 'cardInPickNotif', clienttranslate('You pick ${nbCards} card(s) from ${from}'), [
    //         'playerId' => $player_id,
    //         'nbCards' => count($cards),
    //         'from' => $from,
    //     ]);

    //     self::notifyAll('cardInPickNotifNotif', clienttranslate('${player_name} picks ${nbCards} card(s) from ${from}'), [
    //         'playerId' => $player_id,
    //         'player_name' => self::getPlayerName($player_id),
    //         'nbCards' => count($cards),
    //         'from' => $from,
    //     ]);
    // }
    
    static function replaceCardInPick_AllKnowingAntler() {
        HiddenLeaders::get()->cards->moveAllCardsInLocationKeepOrder('cardInPick', 'discard');
        
        //$cards = array_reverse(self::getCardsInfos(HiddenLeaders::get()->cards->getCardsInLocation('discard')));
        $cards = HeroCard::onlyIds(HiddenLeaders::get()->cards->getCardsInLocation('discard'));

        //usort($cards, fn($a, $b) => $a->location_arg > $b->location_arg);
        self::notifyAll('replaceCardInPick', '', [
            'cards' => $cards,
            'wilderness' => WILDERNESS
        ]);
    }
    static function replaceCardInPick_WrappedWarrior() {
        HiddenLeaders::get()->cards->moveAllCardsInLocationKeepOrder('cardInPick', 'graveyard');
        
        //$cards = array_reverse(self::getCardsInfos(HiddenLeaders::get()->cards->getCardsInLocation('graveyard')));
        $cards = self::getCardsInfos(HiddenLeaders::get()->cards->getCardsInLocation('graveyard'));

        //usort($cards, fn($a, $b) => $a->location_arg > $b->location_arg);
        self::notifyAll('replaceCardInPick', '', [
            'cards' => $cards,
            'graveyard' => GRAVEYARD
        ]);
    }
    static function replaceCardInPick_SurprisedSapling(int $target_id) {
        HiddenLeaders::get()->cards->moveAllCardsInLocationKeepOrder('cardInPick', 'hand'.$target_id);
        
        $cards = self::getCardsInfos(HiddenLeaders::get()->cards->getCardsInLocation('hand'.$target_id));

        //usort($cards, fn($a, $b) => $a->location_arg > $b->location_arg);
        self::notifyAll('replaceCardInPick', '', [
            'cards' => $cards,
            'targetId' => $target_id
        ]);
    }

    static function playFateCard(int $player_id, $fate) {
        $faction = key($fate);
        $mvt = reset($fate);
        
        $cards = HiddenLeaders::get()->fate_cards->getCardsInLocation('fateCardInPick');

        $cards = array_map(fn($cards) => new FateCard($cards, true), array_values($cards));

        HiddenLeaders::get()->fate_cards->moveAllCardsInLocation('fateCardInPick', 'fate');
        HiddenLeaders::get()->fate_cards->shuffle('fate');

        self::notifyAll('replaceCardInPick', clienttranslate('A Guardian has been buried, ${player_name} choose a fate card: ${mvt} ${faction_name}'), [
            'player_name' => self::getPlayerName($player_id),
            'cards' => $cards,
            'faction_name' => $faction,
            'mvt' => $mvt,
            'topCard' => HiddenLeaders::get()->getTopFateCard()
        ]);

    }

    static function drawCorruptionToken(int $player_id, $token, $onCorruptionCardId) {
        // $from = 'corruptionCard';
        // $from = 'deck';
        $token = new CorruptionToken($token);
        
        //if($anonymize) {
        self::notify($player_id, 'drawCorruptionToken', clienttranslate('You draw ${corruption} corruption token'), [
            'player_id' => $player_id,
            'player_name' => self::getPlayerName($player_id),
            'token' => $token,
            'corruption' => $token->type_arg,
            'onCorruptionCardId' => $onCorruptionCardId
        ]);


        // self::notifyAll('drawCorruptionToken', clienttranslate('${player_name} draws a corruption token'), [
        //     'player_id' => $player_id,
        //     'player_name' => self::getPlayerName($player_id),
        //     'token' => CorruptionToken::anonymize($token)
        // ]);
        // }
        // else {
        //     self::notifyAll('drawCorruptionToken', clienttranslate('${player_name} draw ${corruption} corruption token'), [
        //         'player_id' => $player_id,
        //         'player_name' => self::getPlayerName($player_id),
        //         'token' => CorruptionToken::onlyId($token),
        //         'corruption' => $token->type_arg
        //     ]);
        // }
    }

    static function moveCorruptionToken(int $player_id, $token, $card, $target_id, $anonymize) {
        $token = new CorruptionToken($token);
        
        if($anonymize && $player_id != $target_id) { // if place to hidden card
            self::notify($player_id, 'moveCorruptionToken', clienttranslate('You move ${corruption} corruption token to ${player_name2}\'s ${hidden} card'), [
                'player_id' => $player_id,
                'player_name2' => self::getPlayerName($target_id),
                'token' => $token,
                'corruption' => $token->type_arg,
                'card' => HeroCard::anonymize($card),
                'hidden' => HIDDEN
            ]);
            self::notify($target_id, 'moveCorruptionToken', clienttranslate('${player_name} moves a corruption token to ${hidden} ${card_name}'), [
                'player_id' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'token' => CorruptionToken::anonymize($token),
                'card_' => $card,
                'card_name' => $card->name,
                'hidden' => HIDDEN
            ]);

            self::notifyAll('moveCorruptionToken', clienttranslate('${player_name} moves a corruption token to ${player_name2}\'s ${hidden} card'), [
                'player_id' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'player_name2' => self::getPlayerName($target_id),
                'token' => CorruptionToken::anonymize($token),
                'card' => HeroCard::anonymize($card),
                'hidden' => HIDDEN
            ]);
        }
        else {
            self::notify($player_id, 'moveCorruptionToken', clienttranslate('You move ${corruption} corruption token to ${card_name}'), [
                'player_id' => $player_id,
                'token' => $token,
                'corruption' => $token->type_arg,
                'card' => $card,
                'card_name' => $card->name
            ]);

            self::notifyAll('moveCorruptionToken', clienttranslate('${player_name} moves a corruption token to ${card_name}'), [
                'player_id' => $player_id,
                'player_name' => self::getPlayerName($player_id),
                'token' => CorruptionToken::anonymize($token),
                'card' => $card,
                'card_name' => $card->name
            ]);
        }
    }

    static function noFaction(int $selected_player, int $faction) {
        self::notifyAll('noFaction', clienttranslate('${player_name} has no ${faction_name} card'), [
            'player_name' => HiddenLeaders::get()->getPlayerName($selected_player),
            'faction_name' => $faction
        ]);
    }

    static function placeGoblinCrytographer(int $player_id, $card, int $player_from, int $player_to) {
        $card = self::getCardInfos($card);

        self::notifyAll('place', clienttranslate('${player_name} places ${card_name} from ${player_name2} into the party of ${player_name3}'), [
            'player_name' => self::getPlayerName($player_id),
            'targetId' => $player_from,
            'player_name2' => self::getPlayerName($player_from),
            'playerId' => $player_to,
            'player_name3' => self::getPlayerName($player_to),
            'card' => $card,
            'card_name' => $card->name,
            'from' => VISIBLE,
            'to' => VISIBLE,
            'from_value' => VISIBLE,
            'to_value' => VISIBLE,
        ]);
    }

    static function handToCardInPick(int $player_id, int $currentPlayer, $card) {
        $card = self::getCardInfos($card);
        
        self::notify($player_id, 'handToCardInPick', clienttranslate('You give ${card_name} to ${player_name2}'), [
            'playerId' => $player_id,
            'player_name2' => HiddenLeaders::get()->getPlayerName($currentPlayer),
            'currentPlayer' => $currentPlayer,
            'card' => $card,
            'card_name' => $card->name,
        ]);
        self::notify($currentPlayer, 'handToCardInPick', clienttranslate('${player_name} gives ${card_name} to you'), [
            'player_name' => HiddenLeaders::get()->getPlayerName($player_id),
            'playerId' => $player_id,
            'currentPlayer' => $currentPlayer,
            'card' => $card,
            'card_name' => $card->name,
        ]);

        self::notifyAll('handToCardInPick', clienttranslate('${player_name} gives a card to ${player_name2}'), [
            'player_name' => HiddenLeaders::get()->getPlayerName($player_id),
            'playerId' => $player_id,
            'player_name2' => HiddenLeaders::get()->getPlayerName($currentPlayer),
            'currentPlayer' => $currentPlayer,
            'card' => HeroCard::anonymize($card)
        ]);
    }

    static function selectArtifact($artifact, int $player_id) {
        self::notifyAll('selectArtifact', clienttranslate('${player_name} selects the artifact ${artifact_name}'), [
            'player_name' => HiddenLeaders::get()->getPlayerName($player_id),
            'playerId' => $player_id,
            'artifact' => $artifact,
            'artifact_name' => $artifact->name,
        ]);
    }

    static function playArtifact($artifact, int $player_id) {
        self::notifyAll('playArtifact', clienttranslate('${player_name} plays his artifact ${artifact_name}'), [
            'player_name' => HiddenLeaders::get()->getPlayerName($player_id),
            'playerId' => $player_id,
            'artifact' => $artifact,
            'artifact_name' => $artifact->name,
        ]);
        
    }

    // static function triggerEnd(int $player_id) {
    //     $cards = self::getCardsInfos(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards'));
    //     $leaders = HiddenLeaders::get()->getLeaders();
        
    //     self::notifyAll('triggerEnd', clienttranslate('${player_name} has triggered the end of the game'), [
    //         'playerId' => $player_id,
    //         'player_name' => self::getPlayerName($player_id),
    //         'cards' => $cards,
    //         'leaders' => $leaders
    //       ]);
    // }

    // static function setPlayerScore(int $playerId, int $amount, $winningFactionId) {
    //     HiddenLeaders::get()->DbQuery("UPDATE player SET `player_score` = $amount WHERE player_id = $playerId");
            
    //     self::notifyAll('score', '', [
    //         'playerId' => $playerId,
    //         'player_name' => self::getPlayerName($playerId),
    //         'newScore' => $amount,
    //         'faction' => $winningFactionId
    //     ]);
    // }

    // static function incPlayerScore(int $playerId, int $amount) {
    //     HiddenLeaders::get()->DbQuery("UPDATE player SET `player_score` = `player_score` + $amount,  `player_score_aux` = $amount WHERE player_id = $playerId");
            
    //     self::notifyAll('score', '', [
    //         'playerId' => $playerId,
    //         'player_name' => self::getPlayerName($playerId),
    //         'newScore' => HiddenLeaders::get()->getPlayerScore($playerId),
    //         'incScore' => $amount,
    //     ]);
    // }

    protected static function notify($playerId, $name, $msg, $args = []) {
        self::updateArgs($args);
        HiddenLeaders::get()->notifyPlayer($playerId, $name, $msg, $args);
    }

    static function notifyAll($name, $msg, $args = []) {
        self::updateArgs($args);
        HiddenLeaders::get()->notifyAllPlayers($name, $msg, $args);
    }

    protected static function getPlayerName($player_id) {
        return HiddenLeaders::get()->getPlayerName($player_id);
    }

    protected static function getCardInfos($dbCard) {
        return HiddenLeaders::get()->getCardInfos($dbCard);
    }
    protected static function getCardsInfos($dbCards) {
        return HiddenLeaders::get()->getCardsInfos($dbCards);
    }
    
    protected static function incStat($value, $id, $player_id ) {
        return HiddenLeaders::get()->incStat($value, $id, $player_id );
    }

    /*
    * Automatically adds some standard field about player and/or card
    */
    public static function updateArgs(&$args)
    {
        if (isset($args['card_name'])) {
            $args['i18n'] = array( 'card_name' );
            $c = $args['card'];
            $args['card_faction_icon'] = isset($c->type) ? $c->type : $c['type']; // The substitution will be done in JS format_string_recursive function
            
            $args['preserve'] = ['card_faction_icon'];
        }

        if (isset($args['card_1_name'])) {
            $args['i18n'] = array( 'card_1_name' );
            $c = $args['card_1'];
            $args['card_faction_icon_1'] = isset($c->type) ? $c->type : $c['type'];
            $args['preserve'] = ['card_faction_icon_1'];
        }

        if (isset($args['card_2_name'])) {
            $args['i18n'][] = 'card_2_name';
            $c = $args['card_2'];
            $args['card_faction_icon_2'] = isset($c->type) ? $c->type : $c['type'];
            $args['preserve'][] = 'card_faction_icon_2';
        }

        if(isset($args['card_names'])) {
            
            foreach($args['cards'] as $cardId => $card) {
                $array[$card->name] = $card->type;
            }
            $args['card_faction_icons'] = $array;
            $args['preserve'] = ['card_faction_icons'];
        }
    }
}