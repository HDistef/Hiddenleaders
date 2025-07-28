<?php
trait UtilTrait {

    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////
    function drawLeader() { 
        $leader_array = array_keys($this->LEADER_CARDS);

        if ($this->isFLExpansion()) {
            $this->NB_DRAW_LEADERS = [

                2 => [4,2],
                3 => [3,1],
                4 => [4,2],
                5 => [5,2],
                6 => [6,3]
                
            ];
            $fl_leaders_array = array_rand($this->FL_LEADER_CARDS, $this->NB_DRAW_LEADERS[$this->nbPlayers()][1]);
            
            $leader_array = array_rand($this->LEADER_CARDS, $this->NB_DRAW_LEADERS[$this->nbPlayers()][0]);
            $leader_array = array_merge($leader_array, is_array($fl_leaders_array) ? $fl_leaders_array : [$fl_leaders_array]);
        }
        
        foreach ($this->getPlayersIds() as $player_id) {
            $size = count($leader_array);
            if ($size == 0) {
                trigger_error("drawLeader(): Array is empty", E_USER_WARNING);
                return null;
            }

            $rand_index = bga_rand(0, $size - 1);

            $leader_id = $leader_array[$rand_index];
            
            array_splice($leader_array, $rand_index, 1);
            $sql = "UPDATE player SET player_leader_id = $leader_id WHERE player_id = $player_id";
            self::DbQuery($sql);
            
		    self::setStat( $leader_id, 'leader', $player_id );
        }
    }

    function setupCards() {
        
        $cards_data = $this->HERO_CARDS;
        if ($this->isQueensExpansion()) {
            $cards_data += $this->QUEENS_CARDS;
        }
        if ($this->isFLExpansion()) {
            if(!$this->isArtifacts() && !$this->isCorruption()) {
                unset($this->FL_CARDS[HERO_97]);
                unset($this->FL_CARDS[HERO_104]);
                unset($this->FL_CARDS[HERO_111]);
                unset($this->FL_CARDS[HERO_118]);
            }

            if($this->nbPlayers() == 2) {
                unset($this->FL_CARDS[HERO_93]);
                unset($this->FL_CARDS[HERO_100]);
                unset($this->FL_CARDS[HERO_108]);
                unset($this->FL_CARDS[HERO_113]);
            }

            $cards_data += $this->FL_CARDS;
        }
        
        $deck = array();
        
        foreach($cards_data as $card_id => $card) {
            
            $deck[]  = array ('type' => $card['faction_id'] ,'type_arg' => $card_id, 'nbr' => 1 );
        }
        $this->cards->createCards($deck);
        $this->cards->shuffle('deck');

        // Put Emperor card on Graveyard
        $this->cards->moveCard($this->getEmperor(),'graveyard',0);
        
        $this->debugSetup();

        // Draw 3 cards to tavern
        for ($i = 1; $i <= 3; $i++) {
            $this->cards->pickCardForLocation('deck', 'tavern', $i);
        }

        // Add 5 cards to every player's hand
        $players = self::loadPlayersBasicInfos();

        array_map(fn($player_id) => $this->cards->pickCardsForLocation(5,'deck','hand'.$player_id), array_column($players,'player_id'));
    }
    
    function setupFate() {
        
        $cards_data = $this->FATE_CARDS;
        
        $fate = array();
        
        foreach($cards_data as $card_id => $card) {
            
            $fate[]  = array ('type' => 'fate', 'type_arg' => $card_id , 'nbr' => 1 );
        }
        $this->fate_cards->createCards($fate, 'fate');
        $this->fate_cards->shuffle('fate');

        //$this->setFateCard();
    }
    
    function setupCorruption() {
        
        $corruptions = [];

        foreach($this->CORRUPTION_TOKENS as $token_id => $token) {
            //var_dump($token_id, $token);
            // for ($index = 0; $index < 3; $index++) {
            //     $corruptions[] = [ 'type' => $token, 'type_arg' => $token_id + $index, 'nbr' => 1 ];
            // }
            $corruptions[]  = array ('type' =>  $token_id + 1, 'type_arg' => 0, 'nbr' => 3 );
        }
        $this->corruption_tokens->createCards($corruptions, 'deck');
        $this->corruption_tokens->shuffle('deck');

        $this->corruption_tokens->pickCardForLocation('deck','onCorruptionCard',1);
        $this->corruption_tokens->pickCardForLocation('deck','onCorruptionCard',2);
    }
    
    function isQueensExpansion() {
        return intval($this->getGameStateValue(OPTION_QUEENS)) == 2;
    }
    function isFLExpansion() {
        //return false;
        return intval($this->getGameStateValue(OPTION_FORGOTTEN_LEGENDS)) == 2;
    }
    function isCorruption() {
        return false;
        //return intval($this->getGameStateValue(OPTION_CORRUPTION)) == 2;
    }
    function isArtifacts() {
        return false;
        //return intval($this->getGameStateValue(OPTION_ARTIFACTS)) == 2;
    }

    // CARD //
    function getLocationArg($location) {
        return self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_location = '".$location."' order by card_location_arg desc LIMIT 1");
    }

    function getTopCard($location) {
        return HeroCard::onlyId($this->cards->getCardOnTop($location));
    }
    function getTopFateCard() {
        return $this->isFLExpansion() ? FateCard::onlyId($this->fate_cards->getCardOnTop('fate')) : null;
    }
    function getTopCorruptionToken() {
        return $this->isCorruption() ? FateCard::onlyId($this->corruption_tokens->getCardOnTop('deck')) : null;
    }
    function getnextOnCorruptionCard() {
        
    }
    
    function getRemainingCards($location, $playerId = null) {
        return $playerId ? intval($this->cards->countCardInLocation($location, $playerId)) : intval($this->cards->countCardInLocation($location));
    }

    function getEmperor() {
        return self::getUniqueValueFromDB("SELECT * FROM card WHERE card_type_arg = ".HERO_EMPEROR);
    }
    function getPlayerKeeperOfDiscord() {
        return self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_location = 'visibleCards' AND card_type_arg = ".KeeperOfDiscord);
    }
    function getCardParty($playerId, $cardId) {
        return self::getUniqueValueFromDB("SELECT * FROM card WHERE card_location_arg = $playerId AND card_type_arg = $cardId AND card_location IN ('visibleCards','hiddenCards')");
    }
    function isGuardian($type_arg) {
        if(in_array($type_arg, [HERO_97,HERO_104,HERO_111,HERO_118])) return false;

        return $type_arg >= 91; // 91+ => Guardians
    }

    // Add cards infos which are not in the DB
    function getCardInfos($dbCard) {
        if ($dbCard == null) {
            return null;
        }
        
        return new HeroCard($dbCard, $this->CARDS_DATA);
    }

    function getCardsInfos($dbCards) {
        return array_map(fn($dbCard) => $this->getCardInfos($dbCard), array_values($dbCards));
    }

    function getPlayedCardClass() { 
        $played_card_id = $this->getGameStateValue(CARD_PLAYED);
        $played_card = $this->getCardInfos($this->cards->getCard($played_card_id));
        return HeroCard::getInstanceOfCard($played_card);
    }
    function getPlayedArtifactClass() { 
        $played_artifact_id = $this->getGameStateValue(ARTIFACT_PLAYED);
        $artifact = $this->ARTIFACT_CARDS[$played_artifact_id];
        return ArtifactCard::getInstanceOfArtifact($artifact);
    }

    //////////

    // CARD IN PLAY //
    function getNextCardInPlay() {
        $dbResult = self::getUniqueValueFromDB("SELECT card_id FROM cardInPlay ORDER BY order_id desc LIMIT 1"); 
        self::DBQuery("DELETE FROM cardInPlay order by order_id desc LIMIT 1"); 
        return $dbResult;
    }
    function getCardsInPlay() {
        return self::getCollectionFromDB("SELECT * FROM cardInPlay"); 
    }

    //////////////////
    // FACTION //
    function getFactions() {
        $length = $this->isFLExpansion() ? -1 : -2;
        return array_slice($this->FACTIONS, 0, $length, true);
    }
    
    function getFactionName($factionId) {
        if($factionId == 0) return '';
        return $this->FACTIONS[$factionId];
    }
    
    function getCountFactionPlayer(int $playerId, int $factionId, bool $visibleOnly = true) {
        $cardLocation = $visibleOnly ? "'visibleCards'" : "'visibleCards','hiddenCards'";
        return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE card_location_arg = $playerId AND card_type IN ($factionId,".EMPEROR.") AND card_location IN ($cardLocation)"));
    }

    function getCountGuardianPlayer(int $playerId, bool $visibleOnly = true) {
        $cardLocation = $visibleOnly ? "'visibleCards'" : "'visibleCards','hiddenCards'";
        return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE card_location_arg = $playerId AND card_type_arg >= 91 AND card_type_arg NOT IN (97,104,111,118) AND card_location IN ($cardLocation)"));
    }
    function getCountAllFactionsPlayer(int $playerId, bool $visibleOnly = true) {
        $array = array();
        foreach(array_keys($this->getFactions()) as $faction_id) {
            $array[$faction_id] = $faction_id == GUARDIAN ? $this->getCountGuardianPlayer($playerId, GUARDIAN, $visibleOnly) : $this->getCountFactionPlayer($playerId, $faction_id, $visibleOnly);
        }
        return $array;
        // return array (
        //     UNDEAD => $this->getCountFactionPlayer($playerId, UNDEAD, $visibleOnly),
        //     WATER_FOLK => $this->getCountFactionPlayer($playerId, WATER_FOLK, $visibleOnly),
        //     EMPIRE => $this->getCountFactionPlayer($playerId, EMPIRE, $visibleOnly),
        //     TRIBES => $this->getCountFactionPlayer($playerId, TRIBES, $visibleOnly),
        //     GUARDIAN => $this->getCountGuardianPlayer($playerId, GUARDIAN, $visibleOnly)
        // );
    }

    function checkGameEndSpecialCards(int $playerId, $winning_faction) {

        $leader = $this->getLeaderId($playerId);
        $value = 0;

        if($this->getCardParty($playerId,WellFundedQueen) && in_array($leader, [ENNED, CYRA]) 
            || $this->getCardParty($playerId,QueenOfTheWild) && in_array($leader, [MYRAD, LEMRON]) 
            || $this->getCardParty($playerId,QueenOfTheStreets) && in_array($leader, [PAVYR, XIADUL])) $value = 2;
        
        if($this->getCardParty($playerId,EmperorBestFriend) && $this->getCardParty($playerId,HERO_EMPEROR)) $value += 2;
        
        if($this->getCardParty($playerId,MotherOfGuardians)) $value += ~~($this->getCountGuardianPlayer($playerId, false) / 2);
        
        return $value;
    }
    ////////////

    // PLAYER //
    function getLeaderId(int $playerId) {
        return self::getUniqueValueFromDB("SELECT player_leader_id FROM player WHERE player_id = $playerId");
    }
    function getLeader(int $playerId) {
        $leaderId = self::getUniqueValueFromDB("SELECT player_leader_id FROM player WHERE player_id = $playerId");
        
        $leaders = $this->LEADER_CARDS;
        if ($this->isFLExpansion()) $leaders += $this->FL_LEADER_CARDS;
        
        return $leaders[$leaderId];
    }
    function getLeaders() {
        return self::getCollectionFromDB( "SELECT player_id id, player_leader_id leader_id FROM player order by player_no", true );
    }
    function getPlayerCards($player_id, $location) {
        return $this->cards->getCardsInLocation($location, $player_id);
    }
    function getPlayersIds() {
        return array_keys($this->loadPlayersBasicInfos());
    }
    function getPlayerIdsInOrder()
    {
        $player_ids = $this->getPlayersIds();

        if($this->isSpectator()) return $player_ids;
        
        $rotate_count = array_search(self::getCurrentPlayerId(), $player_ids);
        if ($rotate_count === false) {
            return $player_ids;
        }
        for ($i = 0; $i < $rotate_count; $i++) {
            array_push($player_ids, array_shift($player_ids));
        }
        return $player_ids;
    }
    function nbPlayers() {
        return count($this->getPlayersIds());
    }
    function getPlayerName(int $playerId) {
        return self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = $playerId");
    }

    function getPossibleOpponents() {
        $playersIds = $this->getPlayersIds();

        return array_values(array_filter($playersIds, fn($playerId) => $playerId != $this->getActivePlayerId() ));
    }

    function getPossibleOpponentsLocation() {
        $playersIds = $this->getPlayerIdsInOrder();

        return array_values(array_filter($playersIds, fn($playerId) => 
            $playerId != $this->getActivePlayerId() && (intval($this->cards->countCardInLocation('visibleCards', $playerId)) + intval($this->cards->countCardInLocation('hiddenCards', $playerId)) > 0)
        ));
    }
    ///////////

    // TOKEN //
    public function checkTokenLimit($token_mvt, $token_pos) {
        $new_token_pos = $token_pos + $token_mvt;
        
        if($new_token_pos > 12) $token_mvt = $token_mvt - ($new_token_pos - 12);
        if($new_token_pos < 1) $token_mvt = $token_mvt + (1 - $new_token_pos);

        return $token_mvt;
    }

    public function getTokenAhead() {
        $empire_pos = $this->getGameStateValue(EMPIRE_TOKEN);
        $tribes_pos = $this->getGameStateValue(TRIBES_TOKEN);

        if($empire_pos > $tribes_pos) return 1;
        else if($tribes_pos > $empire_pos) return 2;
        return 0;
    }

    public function getWinningFaction() {
        $empire_token = $this->getGameStateValue(EMPIRE_TOKEN);
        $tribes_token = $this->getGameStateValue(TRIBES_TOKEN);

        $guardian_token = $this->getGameStateValue(GUARDIAN_TOKEN);
        $hasGuardianPlayer = self::getUniqueValueFromDB("SELECT COUNT(0) FROM player WHERE player_leader_id >= 7") > 0;

        if($hasGuardianPlayer && $guardian_token > $empire_token && $guardian_token > $tribes_token) return GUARDIAN;
        
        // Undead
        // if the red and green power markers are both on the dark war spaces on the tracker. Note: Undead victory trumps the winning conditions of the other 3 factions
        if($empire_token >= 9 && $tribes_token >= 9) return UNDEAD;
        
        // Water Folk
        // if the red and green power markers are on spaces next to each other or on the same space
        else if (abs($empire_token - $tribes_token) <= 1) return WATER_FOLK;
        
        // Empire
        // if the red marker is at least 2 steps ahead of the green marker
        else if ($empire_token - $tribes_token >= 2) return EMPIRE;
        
        // Tribes
        // if the green marker is at least 2 steps ahead of the red marker.
        else return TRIBES;

        return 0;
    }


    ///////////

    public function getStateName() {
        $state = $this->gamestate->state();
        return $state['name'];
    }

    // SCORE //
    // Number of visible heroes needed to trigger end game
    function getMaxCardsEndGame() {
        return $this->END_GAME_NB_VISIBLECARDS[count($this->getPlayersIds())];
    }
    function getPlayerScore(int $playerId) {
        return intval($this->getUniqueValueFromDB("SELECT player_score FROM player where `player_id` = $playerId"));
    }
    // // set score
    // function dbSetScore($player_id, $count) {
    //     $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    // }
    // // inc score
    // function dbIncScore($player_id, $inc) {
    //     $this->DbQuery("UPDATE player SET player_score= player_score + '$inc' WHERE player_id='$player_id'");

    //     self::notifyAllPlayers('score', '', [
    //         'score' => $this->dbGetScore($player_id),
    //         'playerId' => $player_id
    //     ]);
    // }
    // set aux score (tie breaker)
    function dbSetAuxScore($player_id, $score) {
        $this->DbQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
    }
    ///////////

    function actionUndo() {
        $this->undoRestorePoint();
    }

    // ARRAY FUNCTIONS
    function array_find(array $array, callable $fn) {
        foreach ($array as $value) {
            if($fn($value)) {
                return $value;
            }
        }
        return null;
    }

    function array_find_key(array $array, callable $fn) {
        foreach ($array as $key => $value) {
            if($fn($value)) {
                return $key;
            }
        }
        return null;
    }

    function array_some(array $array, callable $fn) {
        foreach ($array as $value) {
            if($fn($value)) {
                return true;
            }
        }
        return false;
    }
    
    function array_every(array $array, callable $fn) {
        foreach ($array as $value) {
            if(!$fn($value)) {
                return false;
            }
        }
        return true;
    }

    function array_identical(array $a1, array $a2) {
        if (count($a1) != count($a2)) {
            return false;
        }
        for ($i=0;$i<count($a1);$i++) {
            if ($a1[$i] != $a2[$i]) {
                return false;
            }
        }
        return true;
    }
}
