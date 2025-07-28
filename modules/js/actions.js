class ActionManager {
    game;
    current_card;
    action;
    description;
    action_args;
    take_action;

    constructor(game) { this.game = game; }

    reset() {
        this.current_card = null;
        this.action = null;
        this.action_args = [];
        this.description = null;
        return this;
    }

    addAction(card, action) {
        this.current_card = card;
        this.action = action;
        return this;
    }

    addDescription(description) {
        this.description = description;
        return this;
    }

    addArgument(args) {
        this.action_args = args;
        return this;
    }

    activateNextAction() {
        if (this.action) {
            // Invoke action
            const nextAction = this.action;
            //if (this.descriptions.length > 0) this.description = this.descriptions.shift();

            this[nextAction]();
            return;
        }
    }
    
    select_card() {
        //const canPass = ['GoblinCrytographer'].includes(this.current_card.class);

        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                canPass: this.current_card.canPass,
                takeAction: 'selectCard',
                nb_cards_to_select: 1,
                tavern:   this.game.TAVERN,
                visible :   this.game.VISIBLE,
                player :   this.game.PLAYER,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                artifact_name: this.current_card.name
            },
        });
    }
    
    move_card() {
        //const canPass = ['GiganticDuo'].includes(this.current_card.class);

        var nb_cards_to_select = this.current_card.nb_cards_to_select; 
        if(nb_cards_to_select == null) nb_cards_to_select = 1;
        if(nb_cards_to_select > this.action_args.selectable_cards.length) nb_cards_to_select = this.action_args.selectable_cards.length;
        
        const clientStates = Array.isArray(this.action_args.selectable_cards[0]) ? states.client.multiSelectCard : states.client.selectCard;

        this.game.setClientState(clientStates, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                takeAction: 'moveCard',
                canPass: this.current_card.canPass,
                nb_cards_to_select: nb_cards_to_select,
                visible:   this.game.VISIBLE,
                hidden:   this.game.HIDDEN,
                player:   this.game.PLAYER,
                tavern:   this.game.TAVERN,
                graveyard : this.game.GRAVEYARD,
                empire:   this.game.EMPIRE,
                tribes:   this.game.TRIBES,
                undead:   this.game.UNDEAD,
                water_folk:   this.game.WATER_FOLK,
                guardian:   this.game.GUARDIAN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                player_ids: [this.action_args.current_player],
                target_id: this.action_args.target_id,
                card_class: this.current_card.class,
                pick_card: false,
                artifact_name: this.current_card.name
            },
        });
    }
    
    move_card_GamblingOverseer_bury() {
        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                takeAction: 'moveCard',
                nb_cards_to_select: 1,
                hidden:   this.game.HIDDEN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }
    move_card_GamblingOverseer_hand() {
        var nb_cards_to_select = this.action_args.selectable_cards.length >= 2 ? 2 : 1;
        
        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                takeAction: 'moveCard',
                nb_cards_to_select: nb_cards_to_select,
                hidden:   this.game.HIDDEN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    discard() {
        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                takeAction: 'discard',
                nb_cards_to_select: 1,
                undead:   this.game.UNDEAD,
                tribes:   this.game.TRIBES,
                tavern:   this.game.TAVERN,
                empire_token: 'empire-token-log',
                tribes_token: 'tribes-token-log',
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }
    pick_card() {
        var nb_cards_to_select = this.current_card.nb_cards_to_select; 
        if(nb_cards_to_select == null) nb_cards_to_select = 1;
        if(nb_cards_to_select > this.action_args.selectable_cards.length) nb_cards_to_select = this.action_args.selectable_cards.length;
        
        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                takeAction: 'moveCard',
                canPass: this.current_card.canPass,
                nb_cards_to_select: nb_cards_to_select,
                visible:   this.game.VISIBLE,
                hidden:   this.game.HIDDEN,
                player:   this.game.PLAYER,
                tavern:   this.game.TAVERN,
                graveyard : this.game.GRAVEYARD,
                wilderness:   this.game.WILDERNESS,
                empire:   this.game.EMPIRE,
                tribes:   this.game.TRIBES,
                undead:   this.game.UNDEAD,
                water_folk:   this.game.WATER_FOLK,
                guardian:   this.game.GUARDIAN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                player_ids: [this.action_args.current_player],
                target_id: this.action_args.target_id,
                card_class: this.current_card.class,
                pick_card: true,
                artifact_name: this.current_card.name,
            },
        });
    }

    look_card() {
        //const canPass = ['CuriousTroll','WatchfulWitch'].includes(this.current_card.class);

        var nb_cards_to_select = this.action_args.selectable_cards.length >= 2 ? 2 : 1;

        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                canPass : this.current_card.canPass,
                takeAction: 'lookCard',
                nb_cards_to_select: nb_cards_to_select,
                hidden:   this.game.HIDDEN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                artifact_name: this.current_card.name
            },
        });
    }

    end_look_card() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.action_args.selectable_cards.forEach(card => {
            this.game.cardsManager.setCardVisible(card,true);
            this.game.cardsManager.getCardStock(card).setOpened?.(true);
        });

        this.game.addPrimaryActionButton(`end_look_card`, _("OK"), () => {
            this.action_args.selectable_cards.forEach(card => {
                this.game.cardsManager.setCardVisible({id : card.id},false);
                this.game.cardsManager.getCardStock({id :card.id}).setOpened?.(false);
            });
            this.game.takeAction("nextEffect", {});
        });
    }
    
    switch_player() {
        this.game.changePageTitleCardEffect(this.description);

        if(this.action_args.selectable_opponents.length == 1) {
            this.game.takeAction('switchPlayer', {
                target_id : this.action_args.selectable_opponents
            });
            return;
        }

        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('switch_player_' + target_id, this.game.getPlayerName(target_id), () => {
                this.game.takeAction('switchPlayer', {
                    target_id
                });
            });
        });
    }

    exchange() {
        //const canPass = ['UnderestimatedSquire','QueerQuartermaster','MummyMystic'].includes(this.current_card.class);
        var pick_card = false;
        if(this.current_card.class == 'FirmFishmonger') pick_card = true;

        var nb_cards_to_select = this.current_card.nb_cards_to_select; 
        if(nb_cards_to_select == null) nb_cards_to_select = 1;
        if(nb_cards_to_select > this.action_args.selectable_cards[0].length) nb_cards_to_select = this.action_args.selectable_cards[0].length;
        if(nb_cards_to_select > this.action_args.selectable_cards[1].length) nb_cards_to_select = this.action_args.selectable_cards[1].length;
        
        this.game.setClientState(states.client.multiSelectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                nb_cards_to_select: nb_cards_to_select,
                canPass: this.current_card.canPass,
                visible:   this.game.VISIBLE,
                hidden:   this.game.HIDDEN,
                hand:   this.game.HAND,
                tavern:   this.game.TAVERN,
                graveyard:   this.game.GRAVEYARD,
                empire:   this.game.EMPIRE,
                tribes:   this.game.TRIBES,
                water_folk:   this.game.WATER_FOLK,
                undead:   this.game.UNDEAD,
                guardian:   this.game.GUARDIAN,
                player:   this.game.PLAYER,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                takeAction: 'exchange',
                pick_card: pick_card,
                artifact_name: this.current_card.name
            },
        });
    }

    select_faction() {
        this.game.changePageTitleCardEffect(this.description);

        Object.entries(this.action_args.selectable_factions).forEach(faction => { 
            const [faction_id, name] = faction;
            
            this.game.addImageActionButton('select_faction_' + faction_id, `<span class='card-icon faction faction-${faction_id}'></span>`, () => {
                this.game.takeAction('selectFaction', {
                    faction_id
                });
            });
        });

    }
    
    draw_opponent() {
        this.game.changePageTitleCardEffect(this.description);

        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id), () => {
                this.game.takeAction('drawCardFromOpponent', {
                    target_id
                });
            });
        });
    }

    select_opponent() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id), () => {
                this.game.takeAction('selectOpponent', {
                    target_id
                });
            });
        });
    }

    moveToken() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken',
                token_action: this.current_card.token_action,
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    // Card specific action
    moveToken_HairyHermit() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_HairyHermit',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    moveToken_AceFighter() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_AceFighter',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    moveToken_DoubtfulPriest() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_DoubtfulPriest',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    moveToken_SaberToothedTroll() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_SaberToothedTroll',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });

    }

    moveToken_DrownedDeserter() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_DrownedDeserter',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }
    
    moveToken_TripleSwordLizard() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_TripleSwordLizard',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                tokenAhead: this.action_args.tokenAhead,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }
    
    moveToken_SnappySeaSnake() {
        this.game.setClientState(states.client.moveToken, {
            descriptionmyturn: _("${card_name} - Move token :"),
            args : {
                action: 'moveToken_SnappySeaSnake',
                empireToken: this.action_args.empireToken,
                tribesToken: this.action_args.tribesToken,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    select_opponent_PotatoPrivateer() {
        this.game.changePageTitleCardEffect(this.description);

        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id) + ' : ' + this.game.empireCounters[target_id].getValue(), () => {
                
                var tribes_mvt = this.game.empireCounters[target_id].getValue();

                tribes_mvt = tribes_mvt > 3 ? 3 : tribes_mvt;

                tribes_mvt = this.game.checkTokenLimit(tribes_mvt, this.action_args.tribesToken);
                
                this.game.takeAction('moveToken', {
                    empire_mvt: 0,
                    tribes_mvt: tribes_mvt,
                });
            });
        });
    }

    select_opponent_AlmostEvilScholar() {
        this.game.changePageTitleCardEffect(this.description);
        
        // get target from select opponent client state
        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id) + ' : ' + this.game.tribesCounters[target_id].getValue(), () => {
                
                var empire_mvt = this.game.tribesCounters[target_id].getValue();

                empire_mvt = empire_mvt > 3 ? 3 : empire_mvt;

                empire_mvt = this.game.checkTokenLimit(empire_mvt, this.action_args.empireToken);

                this.game.takeAction('moveToken', {
                    empire_mvt: empire_mvt,
                    tribes_mvt: 0
                });
            });
        });
    }

    select_opponent_KrillKeeper() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id) + ' : ' + (- this.game.undeadCounters[target_id].getValue()), () => {
                
                var empire_mvt = - this.game.undeadCounters[target_id].getValue();
                empire_mvt = empire_mvt < -3 ? -3 : empire_mvt;
                empire_mvt = this.game.checkTokenLimit(empire_mvt, this.action_args.empireToken);

                var tribes_mvt = - this.game.undeadCounters[target_id].getValue();
                tribes_mvt = tribes_mvt < -3 ? -3 : tribes_mvt;
                tribes_mvt = this.game.checkTokenLimit(tribes_mvt, this.action_args.tribesToken);

                
                this.game.takeAction('moveToken', {
                    empire_mvt,
                    tribes_mvt
                });
            });
        });
    }

    select_opponent_OppressedOcean() {
        this.game.changePageTitleCardEffect(this.description);

        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id) + ' : ' + this.game.guardianCounters[target_id].getValue(), () => {
                
                var mvt = - this.game.guardianCounters[target_id].getValue();

                mvt = mvt < -4 ? -4 : mvt;

                this.game.setClientState(states.client.moveToken, {
                    descriptionmyturn: _("${card_name} - Move token :"),
                    args : {
                        action: 'moveToken_OppressedOcean',
                        empire_mvt: this.game.checkTokenLimit(mvt, this.action_args.empireToken),
                        tribes_mvt: this.game.checkTokenLimit(mvt, this.action_args.empireToken),
                        card_name: this.current_card.name,
                        card_faction_icon: this.current_card.type
                    },
                });
            });
        });
    }
    
    select_opponent_GoblinCrytographer() {
        this.game.changePageTitleCardEffect(this.description);

        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id), () => {
                this.game.takeAction('selectOpponent', {
                    target_id
                });
            });
        });
    }
    
    select_opponent_SurprisedSapling() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.action_args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id), () => {
                this.game.takeAction('surprisedSapling', {
                    target_id
                });
            });
        });
    }

    draw_MiniatureMerman() {
        this.game.changePageTitleCardEffect(this.description);
        
        const drawCardConfirm = (location) => {
            this.game.makeDiscardSelectable(false);
            this.game.makeGraveyardSelectable(false);
            document.getElementById('graveyard_cards').onclick = null;
            document.getElementById('discard').onclick = null;
            
            this.game.takeAction("drawCard", {
                location,
                nb_cards:2
            });
        };
        
        if(this.action_args.remainingCardsInGraveyard > 0) {
            this.game.makeGraveyardSelectable(true);
            document.getElementById('graveyard_cards').onclick = () => drawCardConfirm('graveyard');
            this.game.addImageActionButton(`drawCardFromGraveyard_button`, `<span class='card-icon icon icon-${this.game.GRAVEYARD}'></span>`, () => drawCardConfirm('graveyard'), 'Graveyard');
        }
        if(this.action_args.remainingCardsInDiscard > 0) {
            this.game.makeDiscardSelectable(true);
            document.getElementById('discard').onclick =  () => drawCardConfirm('discard');
            this.game.addImageActionButton(`drawCardFromDiscard_button`, `<span class='card-icon icon icon-${this.game.WILDERNESS}'></span>`, () => drawCardConfirm('discard'), 'Discard');
        }
    }

    draw_BloomingBag() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.game.makeTavernSelectable(true);
        const drawCardFromTavernConfirm = (card) => {
            if(card === null) return;
            this.game.makeTavernSelectable(false);
            this.decks.tavern_cards.onCardClick = null;
            
            this.game.takeAction("drawCardFromTavern", {
            id: card.id
            });
        };
        this.decks.tavern_cards.onCardClick = card => drawCardFromTavernConfirm(card);

        const drawCardConfirm = (location) => {
            this.game.makeGraveyardSelectable(false);
            document.getElementById('graveyard_cards').onclick = null;
            
            this.game.takeAction("drawCard", {
                location,
                nb_cards:1
            });
        };
        
        if(this.action_args.remainingCardsInGraveyard > 0) {
            this.game.makeGraveyardSelectable(true);
            document.getElementById('graveyard_cards').onclick = () => drawCardConfirm('graveyard');
            //this.game.addImageActionButton(`drawCardFromGraveyard_button`, `<span class='card-icon icon icon-${this.game.GRAVEYARD}'></span>`, () => drawCardConfirm('graveyard'), 'Graveyard');
        }
    }

    action_BludgeoningBlowfish() {
        this.game.setClientState(states.client.bludgeoningBlowfish, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards,
                hidden:   this.game.HIDDEN,
                player:   this.game.PLAYER,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type
            },
        });
    }

    // action_FirmFishmonger() {
    //     this.game.setClientState(states.client.firmFishmonger, {
    //         descriptionmyturn: _(this.description),
    //         args : {
    //             selectable_cards: this.action_args.selectable_cards,
    //             hidden:   this.game.HIDDEN,
    //             card_name: this.current_card.name,
    //             card_faction_icon: this.current_card.type
    //         },
    //     });
    // }

    action_ConfusingCrystal() {
        this.game.changePageTitleCardEffect(this.description);
        console.log(this.action_args.selectable_cards);
        
        this.action_args.selectable_cards.forEach(card => {
            this.game.cardsManager.setCardVisible(card,true);
            this.game.cardsManager.getCardStock(card).setOpened?.(true);
        });

        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: this.action_args.selectable_cards.map(card => card.id),
                takeAction: 'selectCard',
                canPass: 0,
                nb_cards_to_select: 1,
                hidden:   this.game.HIDDEN,
                player:   this.game.PLAYER,
                confusingCrystal: true,
                artifact_name: this.current_card.name
            },
        });
    }

    action_TwiggyTreeKeeper() {
        this.game.changePageTitleCardEffect(this.description);
        
        this.action_args.selectable_cards.forEach(card => {
            this.game.cardsManager.setCardVisible(card,true);
            this.game.cardsManager.getCardStock(card).setOpened?.(true);
        });
        var ids = [];
        Object.values(this.game.gamedatas.players).forEach(player => Object.values(player.hiddenCards).forEach(card => ids.push(card.id)));
        console.log(ids);
        this.game.setClientState(states.client.selectCard, {
            descriptionmyturn: _(this.description),
            args : {
                selectable_cards: ids,
                takeAction: 'moveCard',
                canPass: 0,
                nb_cards_to_select: 1,
                hidden:   this.game.HIDDEN,
                card_name: this.current_card.name,
                card_faction_icon: this.current_card.type,
                player_ids: [this.action_args.current_player]
            },
        });
    }

    action_end_TwiggyTreeKeeper() {
        this.action_args.selectable_cards.forEach(card => {
            this.game.cardsManager.setCardVisible({id : card.id},false);
            this.game.cardsManager.getCardStock({id :card.id}).setOpened?.(false);
        });
        this.game.takeAction("nextEffect", {});
    }
}