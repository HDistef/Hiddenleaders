<?php
class LeaderCard { 
    public string $name;
    public string $description;
    public int $card_value;
    public array $faction_ids;

    public function __construct(string $name, array $faction_ids, string $description, int $card_value) {
        $this->name = $name;
        $this->description = $description;
        $this->faction_ids = $faction_ids;
        $this->card_value = $card_value;
    } 
}

class FateCard {
    public int $id;
    public /*int|null*/ $type_arg;

    public function __construct($dbCard, $anonymize) {
        $this->id = intval($dbCard['id']);

        if($anonymize == false) {
            $this->type_arg = intval($dbCard['type_arg']);
        }
    }

    public static function onlyId($dbCard) {
        if ($dbCard == null) return null;
        
        return new FateCard($dbCard, true);
    }
}

class ArtifactCard {
    public int $id;
    public string $name;
    public string $class;
    public int $uses;
    public int $cardNumber;
    public /*string|null*/ $timing;
    public string $description;
    
    /* args for artifact effect */
    public array $jsactions = [];
    public array $jsdescriptions = [];
    public array $phpactions = [];
    public array $selectableCards = [];
    public array $selectableOpponents = [];

    public function __construct(int $id, string $name, string $class, int $uses, int $cardNumber, string $timing, string $description) {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->uses = $uses;
        $this->cardNumber = $cardNumber;
        $this->timing = $timing;
        $this->description = $description;
    }
    public function __constructClass(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public static function setupArtifacts()
    {
        $array = array_slice(HiddenLeaders::get()->ARTIFACT_CARDS, 0, 6);

        $sql = 'INSERT INTO artefact (artefact_id, artefact_uses, artefact_player_id) VALUES ';
        $values = [];
        
        foreach (HiddenLeaders::get()->getPlayersIds() as $player_id) {
            $size = count($array);
            $rand_index = bga_rand(0, $size - 1);
            
            $artifact_number = $array[$rand_index]->cardNumber;

            array_splice($array, $rand_index, 1);
            
            $artifacts = array_filter(HiddenLeaders::get()->ARTIFACT_CARDS, fn($artifact) => $artifact->cardNumber == $artifact_number);
            
            foreach ($artifacts as $artifact_id => $artifact) {
                $values[] = "($artifact_id, $artifact->uses, $player_id)";
            }
        }
        
        HiddenLeaders::Query($sql . implode(',', $values));
    }

    public static function initCard(int $artifact_id, int $player_id) {
        $artifact = HiddenLeaders::get()->ARTIFACT_CARDS[$artifact_id];
        HiddenLeaders::Query("DELETE FROM artefact WHERE artefact_player_id = $player_id AND artefact_id <> $artifact_id");
        
        return $artifact;
    }

    public static function getArtifact(int $player_id) {
        $dbArtifacts = HiddenLeaders::get()->getObjectList("SELECT * FROM artefact WHERE artefact_player_id = $player_id");

        return array_map(fn($artifact) => self::getArtifactInfos($artifact), array_values($dbArtifacts));
    }

    public static function getArtifactInfos($dbArtifact) {
        $artifact = HiddenLeaders::get()->ARTIFACT_CARDS[$dbArtifact['artefact_id']];
        $artifact->uses = $dbArtifact['artefact_uses'];
        return $artifact;
    }

    public static function availability(int $player_id) {
        if(!HiddenLeaders::get()->isArtifacts()) return null;
        
        $dbArtifact = HiddenLeaders::get()->getObject("SELECT artefact_id, artefact_uses FROM artefact WHERE artefact_player_id = $player_id");
        
        $artifact = HiddenLeaders::get()->ARTIFACT_CARDS[$dbArtifact['artefact_id']];
        $artifactClass = self::getInstanceOfArtifact($artifact);
        
        return $dbArtifact['artefact_uses'] > 0 && 
        HiddenLeaders::get()->getGameStateValue(ARTIFACT_PLAYED) == 0 &&
        (count($artifactClass->jsactions) > 0 || count($artifactClass->phpactions) > 0) ? $artifact->timing : null;
    }

    public static function play(int $player_id) {
        HiddenLeaders::Query("UPDATE artefact SET artefact_uses = artefact_uses - 1 WHERE artefact_player_id = $player_id");

        $dbArtifact = HiddenLeaders::get()->getObject("SELECT artefact_id, artefact_uses FROM artefact WHERE artefact_player_id = $player_id");
        $artifact = HiddenLeaders::get()->ARTIFACT_CARDS[$dbArtifact['artefact_id']];
        $artifact->uses = $dbArtifact['artefact_uses'];

        return $artifact;
    }

    public static function getPlayerShieldOfEndurance() {
        return self::getUniqueValueFromDB("SELECT artefact_player_id FROM artefact WHERE artefact_id = 3");
    }
    public static function getInstanceOfArtifact($artifact) {
        $className = $artifact->class;
        return new $className($artifact->id, $artifact->name);
    }
    public function getGameStateValue($gameStateValue) {
        return HiddenLeaders::get()->getGameStateValue($gameStateValue);
    }
    public function setGameStateValue($gameStateValue, $newValue) {
        return HiddenLeaders::get()->setGameStateValue($gameStateValue, $newValue);
    }
    public function getActivePlayerId() {
        return HiddenLeaders::get()->getActivePlayerId();
    }
    public function getPossibleOpponents() {
        return HiddenLeaders::get()->getPossibleOpponents();
    }
}

class HeroCard {  
    public int $id;
    public string $location;
    public int $location_arg;
    
    public /*int|null*/ $type;
    public /*int|null*/ $type_arg;
    public /*string|null*/ $name;
    public /*string|null*/ $faction_name;
    public string $description_token = '';
    public string $description = '';
    public /*string|null*/ $msg;
    public /*string|null*/ $class;
    public /*boolean|null*/ $isYellow;
    public /*boolean|null*/ $isGuardian;
    public int $nb_cards_to_select = 1;
    public /*boolean|null*/ $canPass;
    
    /* args for cards effect */
    public array $jsactions = [];
    public array $jsdescriptions = [];
    public array $phpactions = [];
    public array $selectableCards = [];
    public array $selectableOpponents = [];
    public array $token_action = [];
    /////////////////

    public function __construct($dbCard, $cards_data) {
        $this->id = intval($dbCard['id']);
        $this->location = $dbCard['location'];
        $this->location_arg = intval($dbCard['location_arg']);

        if($cards_data != null) {
            $this->type = intval($dbCard['type']); // faction_id
            $this->type_arg = intval($dbCard['type_arg']);

            foreach($cards_data as $card_id => $card) {
                if($card_id == $this->type_arg) {
                    $this->name = $card['name'];
                    $this->class = $card['class'];
                    if(isset($card['description_token'])) $this->description_token = $card['description_token'];
                    if(isset($card['description'])) $this->description = $card['description'];

                    if(isset($card['msg'])) $this->msg = $card['msg'];
                    if(isset($card['token_action'])) $this->token_action = $card['token_action'];
                    if(isset($card['isYellow'])) $this->isYellow = $card['isYellow'];
                    if(isset($card['nb_cards_to_select'])) $this->nb_cards_to_select = $card['nb_cards_to_select'];
                    if(isset($card['canPass'])) $this->canPass = $card['canPass'];
                    
                    if(isset($card['isGuardian'])) $this->isGuardian = $card['isGuardian'];
                    
                    $this->faction_name = self::getFactionName($dbCard['type']);
                }
            }
        }
    }

    public function moveCard($args) {
        throw new BgaSystemException('Not implemented : moveCard of ' . get_class($this));
    }
    public function drawCardFromOpponent($args) {
        throw new BgaSystemException('Not implemented : drawCardFromOpponent of ' . get_class($this));
    }
    public function exchange($args) {
        throw new BgaSystemException('Not implemented : exchange of ' . get_class($this));
    }
    
    public function getGameStateValue($gameStateValue) {
        return HiddenLeaders::get()->getGameStateValue($gameStateValue);
    }
    public function setGameStateValue($gameStateValue, $newValue) {
        return HiddenLeaders::get()->setGameStateValue($gameStateValue, $newValue);
    }
    public function incGameStateValue($gameStateValue, $incValue) {
        return HiddenLeaders::get()->incGameStateValue($gameStateValue, $incValue);
    }

    public function getFactionName($factionId) {
        return HiddenLeaders::get()->getFactionName($factionId);
    }

    public function getPlayersIds() {
        return HiddenLeaders::get()->getPlayerIdsInOrder();
    }
    public function getActivePlayerId() {
        return HiddenLeaders::get()->getActivePlayerId();
    }
    public function getPossibleOpponents() {
        return HiddenLeaders::get()->getPossibleOpponents();
    }
    public function getPossibleOpponentsLocation() {
        return HiddenLeaders::get()->getPossibleOpponentsLocation();
    }

    public function selectOpponentCards(string $location) {
        foreach($this->getPossibleOpponents() as $player_id) {
            $this->selectableCards = array_merge($this->selectableCards, array_keys(HiddenLeaders::get()->cards->getCardsInLocation($location, $player_id)));
        }

        // $this->selectableCards = array_merge($this->selectableCards, array_map(
        //     fn($player_id) => array_keys(HiddenLeaders::get()->cards->getCardsInLocation($location, $player_id)), $this->getPossibleOpponents()
        // ));
    }
    public function selectOneOpponentCards(int $player_id) {
        $this->selectableCards = array_merge(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $player_id)), array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards', $player_id)));
    }
    
    public function getLocationArg($location) {
        return HiddenLeaders::get()->getLocationArg($location);
    }

    public function getCardInfos($card) {
        return HiddenLeaders::get()->getCardInfos($card);
    }

    public function getCardsInfos($dbCards) {
        return HiddenLeaders::get()->getCardsInfos($dbCards);
    }

    public function incStat($value, $id, $player_id ) {
        return HiddenLeaders::get()->incStat($value, $id, $player_id );
    }

    public static function onlyId($dbCard) {
        if ($dbCard == null) {
            return null;
        }
        
        return new HeroCard($dbCard, null);
    }

    public static function onlyIds(array $dbCards) {
        return array_map(fn($dbCard) => self::onlyId($dbCard), $dbCards);
    }
    
    public static function anonymize($heroCard) {
        
        $card['id'] = $heroCard->id;
        $card['location'] = $heroCard->location;
        $card['location_arg'] = $heroCard->location_arg;

        return new HeroCard($card, null);
    }

    /**
     * @return HeroCard
     */
    public static function getInstanceOfCard($card) {
        /** @var HeroCard */
        $className = $card->class;
        $cardClass = new $className(HiddenLeaders::get()->cards->getCard($card->id), HiddenLeaders::get()->CARDS_DATA);
        return $cardClass;
    }
}

class CorruptionToken {
    public int $id;
    public string $location;
    public int $location_arg;
    public /*string|null*/ $type;
    public /*int|null*/ $type_arg;

    public function __construct($dbToken, $anonymize = false) {
        $this->id = intval($dbToken['id']);
        $this->type_arg = intval($dbToken['type_arg']); //player id
        $this->location = $dbToken['location']; // deck, onHeroCard, onCorruptionCard
        $this->location_arg = intval($dbToken['location_arg']); // position corruption card or card id

        if(!$anonymize) {
            $this->type = $dbToken['type']; //id type token
        }
    }
    
    public static function anonymize($token) {
        
        $card['id'] = $token->id;
        $card['type_arg'] = $token->type_arg;
        $card['location'] = $token->location;
        $card['location_arg'] = $token->location_arg;

        return new CorruptionToken($card, true);
    }

    public static function onlyId($dbToken) {
        if ($dbToken == null) return null;
        
        return new CorruptionToken($dbToken, true);
    }

    public static function onlyIds(array $dbTokens) {
        return array_map(fn($dbToken) => self::onlyId($dbToken), $dbTokens);
    }
}
?>