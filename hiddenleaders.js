/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HiddenLeaders implementation : © Hervé DI STEFANO hdistef7@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * hiddenleaders.js
 *
 * HiddenLeaders user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo",
    "dojo/_base/declare",
    "dojo/debounce",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    g_gamethemeurl + "modules/js/bga-animations.js",
    g_gamethemeurl + "modules/js/bga-cards.js",
    g_gamethemeurl + "modules/js/bga-zoom.js",
    g_gamethemeurl + "modules/js/bga-jump-to.js",
    g_gamethemeurl + 'modules/js/cards.js',
    g_gamethemeurl + 'modules/js/decks.js',
    g_gamethemeurl + 'modules/js/player-table.js',
    g_gamethemeurl + 'modules/js/actions.js',
    g_gamethemeurl + 'modules/js/state.js',
    g_gamethemeurl + 'modules/js/modal.js',
    g_gamethemeurl + 'modules/js/states/player-setup.js',
    g_gamethemeurl + 'modules/js/states/player-action.js',
    g_gamethemeurl + 'modules/js/states/card-effect.js',
    g_gamethemeurl + 'modules/js/states/draw-card.js',
    g_gamethemeurl + 'modules/js/states/discard.js',
    g_gamethemeurl + 'modules/js/states/move-token.js',
    g_gamethemeurl + 'modules/js/states/select-card.js',
    g_gamethemeurl + 'modules/js/states/multi-select-card.js',
    g_gamethemeurl + 'modules/js/states/pick-card.js',
    g_gamethemeurl + 'modules/js/states/exchange.js',
    g_gamethemeurl + 'modules/js/states/end-score.js',
    g_gamethemeurl + 'modules/js/states/bludgeoning-blowfish.js',
    g_gamethemeurl + 'modules/js/states/will-bending-witch.js',
    g_gamethemeurl + 'modules/js/states/philantropic-phantom.js',
    g_gamethemeurl + 'modules/js/states/hard-shelled-titan.js',
    g_gamethemeurl + 'modules/js/states/firm-fishmonger.js',
    g_gamethemeurl + 'modules/js/states/confusing-crystal.js',
    g_gamethemeurl + 'modules/js/states/fate.js',
    g_gamethemeurl + 'modules/js/states/spread-corruption.js',
    g_gamethemeurl + 'modules/js/states/artifact-setup.js',
    g_gamethemeurl + 'modules/js/states/artifact.js',
],
function (dojo, declare, bgaCards, bgaZoom) {
    return declare("bgagame.hiddenleaders", 
    [
        ebg.core.gamegui,
        hiddenleaders.cards,
    ], {
        constructor: function(){
            console.log('hiddenleaders constructor');

            //this.handCounters = [];
            this.visibleCounters = [];
            this.hiddenCounters = [];
            this.undeadCounters = [];
            this.waterfolkCounters = [];
            this.empireCounters = [];
            this.tribesCounters = [];
            this.guardianCounters = [];

            this.EMPIRE_TOKEN = 1;
            this.TRIBES_TOKEN = 2;
            this.GUARDIAN_TOKEN = 3;
           
            this.UNDEAD = 1;
            this.WATER_FOLK = 2;
            this.EMPIRE = 3;
            this.TRIBES = 4;
            this.GUARDIAN = 5;

            this.YELLOW = 6;
            
            this.GRAVEYARD = 'graveyard';
            this.TAVERN = 'tavern';
            this.HARBOR = 'harbor';
            this.WILDERNESS = 'wilderness';
            this.VISIBLE = 'visible';
            this.HIDDEN = 'hidden';
            this.PLAYER = 'player';
            this.HAND = 'hand';
            // this.GRAVEYARD = 1;
            // this.TAVERN = 2;
            // this.HARBOR = 3;
            // this.WILDERNESS = 4;
            // this.VISIBLE = 5;
            // this.HIDDEN = 6;
            // this.PLAYER = 7;
            // this.HAND = 10;
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            console.log(gamedatas);
            dojo.place(`<div id="card-pick" data-visible="false"></div>`, 'page-title');
            
            this.setupTokens();
            this.animationManager = new AnimationManager(this);
            this.setupCardManager();
            this.stateManager = new StateManager(this);
            this.actionManager = new ActionManager(this);
            //this.modal = new Modal(this);

            //Fate
            if(gamedatas.fateTopCard) dojo.place(`<div id="fateCards"></div>`,'central_area');

            this.decks = new Decks(this, gamedatas);
            
            this.createPlayerPanels();
            this.createPlayerTables();
            
            dojo.place("<div id='customActions' style='display:inline-block'></div>", $('generalactions'), 'after');

            // let html = `<div class='cardToolTip' style="font-size:large">
            //         <div style="text-align:center"><strong>${_("WIN CONDITIONS")}</strong></div>
            //         <div><span class='card-icon faction faction-${this.UNDEAD}'></span><strong style="color: #030303;">${_(this.gamedatas.factions[this.UNDEAD])}</strong>: ${_("Both tokens on spaces in the dark area")}<br><i style="color:blue;font-size: smaller;">${_(`${$undeadFaction} victory trumps the winning conditions of the other 3 factions`)}</i></div>
            //         <div><span class='card-icon faction faction-${this.WATER_FOLK}'></span><strong style="color: #0d66dad5;">${_(this.gamedatas.factions[this.WATER_FOLK])}</strong>: ${_("Tokens are on the same space <strong>OR</strong> next to each other")}</div>
            //         <div><span class='card-icon faction faction-${this.EMPIRE}'></span><strong style="color: #d30606;">${_(this.gamedatas.factions[this.EMPIRE])}</strong>: ${_("Red token is at least 2 steps ahead of the Green")}</div>
            //         <div><span class='card-icon faction faction-${this.TRIBES}'></span><strong style="color: #4fc720;">${_(this.gamedatas.factions[this.TRIBES])}</strong>: ${_("Green token is at least 2 steps ahead of the Red")}</div>
            //     </div>`;
            
            // if(this.GUARDIAN in this.gamedatas.factions) document.getElementById('win-conditions').dataset.expansion = 1;
            // else {
            //     document.getElementById('win-conditions').insertAdjacentHTML(
            //         "afterbegin",
            //         `<div class="gametext">
            //             <div class="gametext-title">${_("WIN CONDITIONS")}</div>
            //             <div class="gametext-description gametext-undead">${_("Both tokens on spaces in the dark area")}</div>
            //             <div class="gametext-description gametext-water_folk">${_("Tokens are on the same space <strong>OR</strong> next to each other")}</div>
            //             <div class="gametext-description gametext-empire"><strong style="color: #d30606;">${_(this.gamedatas.factions[this.EMPIRE])}</strong> ${_("is at least 2 steps ahead of the")} <strong style="color: #4fc720;">${_(this.gamedatas.factions[this.TRIBES])}</strong></div>
            //             <div class="gametext-description gametext-tribes"><strong style="color: #4fc720;">${_(this.gamedatas.factions[this.TRIBES])}</strong> ${_("is at least 2 steps ahead of the")} <strong style="color: #d30606;">${_(this.gamedatas.factions[this.EMPIRE])}</strong></div>
            //         </div>`,
            //     );
            // }

            // let html = `<div class='cardToolTip' style="font-size:large">
            // <div style="text-align:center"><strong>${_("WIN CONDITIONS")}</strong></div>
            // <div><span class='card-icon faction faction-${this.UNDEAD}'></span><strong style="color: #030303;">${_(this.gamedatas.factions[this.UNDEAD])}</strong>: ${_("Both tokens on spaces in the dark area")}
            // <br><i style="color:blue;font-size: smaller;">${dojo.string.substitute( _("${undeadFaction} victory trumps the winning conditions of the other 3 factions"), {
            //     undeadFaction: _(this.gamedatas.factions[this.UNDEAD])
            // })}</i></div>
            // <div><span class='card-icon faction faction-${this.WATER_FOLK}'></span><strong style="color: #0d66dad5;">${_(this.gamedatas.factions[this.WATER_FOLK])}</strong>: ${_("Tokens are on the same space <strong>OR</strong> next to each other")}</div>
            // <div><span class='card-icon faction faction-${this.EMPIRE}'></span><strong style="color: #d30606;">${_(this.gamedatas.factions[this.EMPIRE])}</strong>: ${_("Red token is at least 2 steps ahead of the Green")}</div>
            // <div><span class='card-icon faction faction-${this.TRIBES}'></span><strong style="color: #4fc720;">${_(this.gamedatas.factions[this.TRIBES])}</strong>: ${_("Green token is at least 2 steps ahead of the Red")}</div>`;
            
            // if(this.GUARDIAN in this.gamedatas.factions) html += `<div><span class='card-icon faction faction-${this.GUARDIAN}'></span><strong style="color: #812859;">${_(this.gamedatas.factions[this.GUARDIAN])}</strong>: ${_("Guardian token is ahead of both the Red and Green")}
            // <br><i style="color:blue;font-size: smaller;">${dojo.string.substitute( _("${guardianFaction} victory trumps the winning conditions of all other factions"), {
            //     guardianFaction: _(this.gamedatas.factions[this.GUARDIAN])
            // })}<br>Ignore the Guardian marker if no Leader is supporting the Guardians</i></div>`;
            
            // html += `</div>`;
            
            // this.addTooltipHtml('win-conditions', html, 200);

            //Corruption
            if(gamedatas.onCorruptionCardTokens) {
                html = `<div data-players="${gamedatas.nbPlayers}" id="corruptionCard">`;
                if(typeof gamedatas.onCorruptionCardTokens[0] != 'undefined') html += `<div id="corruptionToken_${gamedatas.onCorruptionCardTokens[0].id}" data-type="null" class="card-icon icons-corruption onCorruptionCard token_${gamedatas.onCorruptionCardTokens[0].location_arg}"></div>`;
                if(typeof gamedatas.onCorruptionCardTokens[1] != 'undefined') html += `<div id="corruptionToken_${gamedatas.onCorruptionCardTokens[1].id}" data-type="null" class="card-icon icons-corruption onCorruptionCard token_${gamedatas.onCorruptionCardTokens[1].location_arg}"></div>`;
                html +=  `</div>`;

                dojo.place(html,'central_area');
                
                gamedatas.onHeroCardTokens.forEach(token => dojo.place(this.createCorruptionToken(token.id, token.type, 'onHeroCard'), `herocard-${token.location_arg}`));
            }
            
            //dojo.place(`div id="winning-conditions"></div>`, 'central_area');
            // document.getElementById("winning-conditions").addEventListener('click', () => document.getElementById("winning-conditions").classList.toggle('folded') );

            //dojo.place(`<div id="endGameCard" data-id=${gamedatas.nbPlayers}></div>`,'central_area');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            this.setupHelper();
            this.setupPreferences();


            this.zoomManager = new ZoomManager({
                element: document.getElementById('all_area'),
                localStorageZoomKey: 'HiddenLeaders-zoom',
                smooth: false,
                zoomControls: {
                    color: 'white',
                },
                defaultZoom: 1,
            });
            this.onScreenWidthChange = () => {
                while (this.zoomManager.zoom > 0.25 && document.getElementById('all_area').clientWidth < 1350) {
                    this.zoomManager.zoomOut();   
                }
                while (this.zoomManager.zoom < 1 && document.getElementById('all_area').clientWidth > 1550) {
                    this.zoomManager.zoomIn();   
                }
            }

            var jumpToEntries = [
                new JumpToEntry(_('Main board'), 'central_area', { 'color': 'darkgray' })
            ];

            this.jumpToManager = new JumpToManager(this, {
                localStorageFoldedKey: 'HiddenLeaders-jump-to-folded',
                topEntries: jumpToEntries,
            });

            if (gamedatas.endGame) { //end
                //this.onEnteringState('endScore',[]);
                this.playersTables.forEach(playerTable => {
                    playerTable.playerHiddenCards.setOpened(true);
        
                    playerTable.playerHiddenCards.getCards().forEach(card => this.incValueFactionCounter(card.type, playerTable.playerId, 1, card.isGuardian));
                });
            }

            console.log( "Ending game setup" );
        },

        /* @Override */
        updatePlayerOrdering() {
            this.inherited(arguments);

            let html = `<div class="player-board" id="player_board_winning_faction">
                <div style="font-weight: 800;">${_("Winning Faction")}</div>
                <div id="player_board_winning_faction_name" data-faction="${this.gamedatas.winningFaction}">${_(this.gamedatas.winningFaction_translated)} <span class="card-icon faction faction-${this.gamedatas.winningFaction}"></span></div>`; 

            if(this.gamedatas.corruptionTopToken) html += `<div id="corruptionTokens"><span class='card-icon icons-corruption icon-0'></span></div>`;
            
            html += `</div>`;

            dojo.place(html, 'player_boards', 'last');

            if(this.gamedatas.corruptionTopToken) {
                this.decks.corruption_tokens = new Deck(this.corruptionTokensManager,document.getElementById('corruptionTokens'), {
                    topCard: this.gamedatas.corruptionTopToken
                });
            }
        },

        getOrderedPlayers() {
            const players = Object.values(this.gamedatas.players);//.sort((a, b) => a.playerNo - b.playerNo);
            const playerIndex = players.findIndex(player => Number(player.id) === Number(this.player_id));
            const orderedPlayers = playerIndex > 0 ? [...players.slice(playerIndex), ...players.slice(0, playerIndex)] : players;
            return orderedPlayers;
        },

        setupPreferences() {
            // Extract the ID and value from the UI control
            const onchange = (e) => {
              var match = e.target.id.match(/^preference_[cf]ontrol_(\d+)$/);
              if (!match) {
                return;
              }
              var prefId = +match[1];
              var prefValue = +e.target.value;
              this.prefs[prefId].value = prefValue;
            }
            
            // Call onPreferenceChange() when any value changes
            dojo.query(".preference_control").connect("onchange", onchange);
            
            // Call onPreferenceChange() now
            dojo.forEach(
              dojo.query("#ingame_menu_content .preference_control"),
              el => onchange({ target: el })
            );
        },

        setupHelper() {
            let helpContents = this.format_block('jstpl_helpModal', {
                player: _('Player'),
                faceup: _('Face-up'),
                facedown: _('Face-down'),
                harbor: _('Harbor'),
                wilderness: _('Wilderness'),
                tavern: _('Tavern'),
                graveyard: _('Graveyard'),
    
                playerText: _('Self or other'),
                faceupText: _('Visible heroes in a player\'s party'),
                facedownText: _('Hidden heroes in a player\'s party'),
                harborText: _('Draw pile'),
                wildernessText: _('Discard pile'),
                tavernText: _('3 slots with face-up heroes. Players can draw from theses cards'),
                graveyardText: _('If a hero is killed, this hero is put on top of the Graveyard face-up. Only the top card is visible'),
                
                undead: _('The Undead'),
                waterfolk: _('Water Folk'),
                empire: _('Imperial Army'),
                tribes: _('Hill Tribes'),
                guardian: _('Guardians'),
            });
    
            this._helperModal = new modal('iconOverview', {
              class: 'hiddenleaders_popin',
              closeIcon: 'fa-times',
              openAnimation: true,
              openAnimationTarget: 'tab-icon-overview',
              contents: helpContents,
              closeAction: 'hide',
              verticalAlign: 'flex-start',
            });
    
            dojo.connect($('tab-icon-overview'), 'onclick', () => this.showHelper());

            let conditionsContents = this.format_block('jstpl_conditionsModal', {
                wincon_undeadText: _('Both tokens on spaces in the dark area'),
                wincon_undeadText2: _('Undead victory trumps the winning conditions of the other 3 factions'),
                wincon_waterfolkText: _('Tokens are on the same space <strong>OR</strong> next to each other'),
                wincon_empireText: _('Red token is at least 2 steps ahead of the Green'),
                wincon_tribesText: _('Green token is at least 2 steps ahead of the Red'),
                wincon_guardianText: _('Guardian token is ahead of both the Red and Green'),
                wincon_guardianText2: _('Guardian victory trumps the winning conditions of all other factions<br>Ignore the Guardian marker if no Leader is supporting the Guardians'),
            });

            this._conditionsModal = new modal('winConditions', {
                class: 'hiddenleaders_popin',
                closeIcon: 'fa-times',
                openAnimation: true,
                openAnimationTarget: 'tab-win-conditions',
                contents: conditionsContents,
                closeAction: 'hide',
                verticalAlign: 'flex-start',
              });
      
              dojo.connect($('tab-win-conditions'), 'onclick', () => this.showConditions());

            if(this.GUARDIAN in this.gamedatas.factions) {
                const nodes = document.querySelectorAll('[data-guardian]');
                for (const node of nodes) {
                    node.dataset.guardian = 1;
                }
            }
        },
        showHelper() {
          this._helperModal.show();
        },
        showConditions() {
          this._conditionsModal.show();
        },

        /** Tells if player board horizontal is active in user prefs. */
		isHorizontal() {
			return this.prefs[1].value == 2;
		},

        createPlayerTables() {
            const orderedPlayers = this.getOrderedPlayers();

            this.playersTables = [];

            this.gamedatas.playersorder.forEach(playerId => {
                this.playersTables.push(new PlayerTable(this, this.gamedatas.players[Number(playerId)]))}
            );
            
            //this.playersTables = [];

            //this.playersTables.push(new PlayerTable(this, this.gamedatas.players[Number(this.player_id)]));

            // Object.values(this.gamedatas.players).forEach(player => {
            //     if(Number(player.id) !== Number(this.player_id)) this.playersTables.push(new PlayerTable(this, this.gamedatas.players[Number(player.id)]));
            // });
        },

        getPlayerId() {
           return Number(this.player_id);
        },
        getPlayerName(playerId) {
           return this.gamedatas.players[Number(playerId)].name;
        },
        getPlayerColor(playerId) {
            return this.gamedatas.players[Number(playerId)].color;
        },
        getCurrentPlayerTable() {
            return this.getPlayerTable(this.getPlayerId());
        },

        getPlayerTable(playerId) {
            return this.playersTables.find((playerTable) => playerTable.playerId == playerId);
        },
        
        closePlayerHiddenCards() {
            this.playersTables.forEach(playerTable => playerTable.playerHiddenCards.setOpened(false));
        },

        createPlayerPanels() {

            Object.values(this.gamedatas.players).forEach(player => {
                const playerId = Number(player.id);   

                var currentPlayer = playerId == this.getPlayerId();

                // hand cards counter
                let html =
                `<div class="counters">
                    <div id="hand-counter-wrapper-${playerId}" class="hand-counter">
                `;
                if(!currentPlayer) html+= `<div id="hand-${playerId}"></div>`;
                    // :`<div class="counter-icon icon hand"></div> 
                    // <span class="counter-value" id="hand-counter-${playerId}"></span>`
                html+= `
                    </div>
                    <div id="visible-counter-wrapper-${playerId}" class="visible-counter">
                        <span class='card-icon icon icon-${this.VISIBLE}'></span>
                        <span class="counter-value" id="visible-counter-${playerId}"></span>
                        <span class="counter-value" style="font-weight:bolder"> / ${this.gamedatas.getMaxCardsEndGame}</span>
                    </div>
                    <div id="hidden-counter-wrapper-${playerId}" class="hidden-counter">
                        <span class='card-icon icon icon-${this.HIDDEN}'></span>
                        <span class="counter-value" id="hidden-counter-${playerId}"></span>
                    </div>
                    <div class="break"></div>
                    <div id="undead-counter-wrapper-${playerId}" class="undead-counter">
                        <span class='card-icon faction faction-${this.UNDEAD}'></span>
                        <span class="counter-value" id="undead-counter-${playerId}"></span>
                    </div>
                    <div id="waterfolk-counter-wrapper-${playerId}" class="waterfolk-counter">
                        <span class='card-icon faction faction-${this.WATER_FOLK}'></span>
                        <span class="counter-value" id="waterfolk-counter-${playerId}"></span>
                    </div>
                    <div id="empire-counter-wrapper-${playerId}" class="empire-counter">
                        <span class='card-icon faction faction-${this.EMPIRE}'></span>
                        <span class="counter-value" id="empire-counter-${playerId}"></span>
                    </div>
                    <div id="tribes-counter-wrapper-${playerId}" class="tribes-counter">
                        <span class='card-icon faction faction-${this.TRIBES}'></span>
                        <span class="counter-value" id="tribes-counter-${playerId}"></span>
                    </div>`;

                if(this.GUARDIAN in player.counterFactions) {
                    html+= `
                        <div id="guardian-counter-wrapper-${playerId}" class="guardian-counter">
                            <span class='card-icon faction faction-${this.GUARDIAN}'></span>
                            <span class="counter-value" id="guardian-counter-${playerId}"></span>
                        </div>
                    `
                }

                html += `</div>`;
                dojo.place(html, `player_board_${playerId}`);
                
                // if(currentPlayer) {
                    //this.handCounters[playerId] = new ebg.counter();
                    //this.handCounters[playerId].create(`hand-counter-${playerId}`);
                    //this.handCounters[playerId].setValue(Object.keys(player.hand).length);
                // }
                this.addTooltipHtmlToClass('hand-counter', _('Cards in hand'));
                
                this.visibleCounters[playerId] = new ebg.counter();
                this.visibleCounters[playerId].create(`visible-counter-${playerId}`);
                this.visibleCounters[playerId].setValue(Object.keys(player.visibleCards).length);
                this.addTooltipHtmlToClass('visible-counter', _('Number of visible cards'));
                
                this.hiddenCounters[playerId] = new ebg.counter();
                this.hiddenCounters[playerId].create(`hidden-counter-${playerId}`);
                this.hiddenCounters[playerId].setValue(Object.keys(player.hiddenCards).length);
                this.addTooltipHtmlToClass('hidden-counter', _('Number of hidden cards'));
                
                this.undeadCounters[playerId] = new ebg.counter();
                this.undeadCounters[playerId].create(`undead-counter-${playerId}`);
                this.undeadCounters[playerId].setValue(player.counterFactions[this.UNDEAD]);
                this.addTooltipHtmlToClass('undead-counter', _('Number of visible Undead'));
                
                this.waterfolkCounters[playerId] = new ebg.counter();
                this.waterfolkCounters[playerId].create(`waterfolk-counter-${playerId}`);
                this.waterfolkCounters[playerId].setValue(player.counterFactions[this.WATER_FOLK]);
                this.addTooltipHtmlToClass('waterfolk-counter', _('Number of visible Water Folk'));
                
                this.empireCounters[playerId] = new ebg.counter();
                this.empireCounters[playerId].create(`empire-counter-${playerId}`);
                this.empireCounters[playerId].setValue(player.counterFactions[this.EMPIRE]);
                this.addTooltipHtmlToClass('empire-counter', _('Number of visible Imperial Army'));
                
                this.tribesCounters[playerId] = new ebg.counter();
                this.tribesCounters[playerId].create(`tribes-counter-${playerId}`);
                this.tribesCounters[playerId].setValue(player.counterFactions[this.TRIBES]);
                this.addTooltipHtmlToClass('tribes-counter', _('Number of visible Hill Tribes'));
                
                if(this.GUARDIAN in player.counterFactions) {
                    this.guardianCounters[playerId] = new ebg.counter();
                    this.guardianCounters[playerId].create(`guardian-counter-${playerId}`);
                    this.guardianCounters[playerId].setValue(player.counterFactions[this.GUARDIAN]);
                    this.addTooltipHtmlToClass('guardian-counter', _('Number of visible Guardians'));
                }
            });
    
        },
        toggleButton(id, enabled) {
            const el = document.getElementById(id);

            //document.getElementById(id)?.classList.toggle(`disabled`, enabled);
            enabled ? el.classList.remove('disabled') : el.classList.add('disabled');
        },

        makeDeckSelectable(selectable) {
            const el = document.getElementById('deck'); 

            if(this.decks.deck.getCardNumber() > 0) this.decks.deck.setSelectionMode(selectable ? 'single' : 'none');
            else {
                selectable ? dojo.addClass(el, 'divSelectable') : dojo.removeClass(el, 'divSelectable');
            }

            if (!selectable && el.classList.contains('divSelectable')) dojo.removeClass(el, 'divSelectable');
        },

        makeGraveyardSelectable(selectable) {
            const el = document.getElementById('graveyard_cards'); 

            if(this.decks.graveyard_cards.getCardNumber() > 0) this.decks.graveyard_cards.setSelectionMode(selectable ? 'single' : 'none');
            else {
                selectable ? dojo.addClass(el, 'divSelectable') : dojo.removeClass(el, 'divSelectable');
            }

            if (!selectable && el.classList.contains('divSelectable')) dojo.removeClass(el, 'divSelectable');
        },

        makeDiscardSelectable(selectable) {
            const el = document.getElementById('discard'); 

            if(this.decks.discard_cards.getCardNumber() > 0) this.decks.discard_cards.setSelectionMode(selectable ? 'single' : 'none');
            else {
                selectable ? dojo.addClass(el, 'divSelectable') : dojo.removeClass(el, 'divSelectable');
            }

            if (!selectable && el.classList.contains('divSelectable')) dojo.removeClass(el, 'divSelectable');
        },

        makeTavernSelectable(selectable, single = true) {
            this.decks.tavern_cards.setSelectionMode(selectable ? single ? 'single' : 'multiple' : 'none');
        },

        makeVisibleSelectable(playerId, selectable) {
            const el = document.getElementById(`player_table_visible_wrapper_${playerId}`); 
            
            if(selectable) dojo.addClass(el, 'divSelectable');
            else dojo.removeClass(el, 'divSelectable');
        },
        
        makeHiddenSelectable(playerId, selectable) {
            const el = document.getElementById(`player_table_hidden_wrapper_${playerId}`); 

            if(selectable) dojo.addClass(el, 'divSelectable');
            else dojo.removeClass(el, 'divSelectable');
        },
        
        setupTokens() {
            var empire_token = $('empire-token');
            var tribes_token = $('tribes-token');


            dojo.removeClass(empire_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");
            dojo.removeClass(tribes_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");

            dojo.addClass(empire_token, "slot-" + this.gamedatas.empireToken);
            dojo.addClass(tribes_token, "slot-" + this.gamedatas.tribesToken);
            
            this.addTooltipHtml('empire-token', `<strong style="color:#d30606">${_(this.gamedatas.factions[this.EMPIRE])}</strong>`, 200);
            this.addTooltipHtml('tribes-token', `<strong style="color:#4fc720">${_(this.gamedatas.factions[this.TRIBES])}</strong>`, 200);

            if(this.GUARDIAN in this.gamedatas.factions) {
                dojo.place(`<div id="guardian-token" class="power-token transition-token"></div>`, 'power-track', 'last');
                var guardian_token = $('guardian-token');
                
                dojo.removeClass(guardian_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");
                dojo.addClass(guardian_token, "slot-" + this.gamedatas.guardianToken);

                this.addTooltipHtml('guardian-token', `<strong>${_(this.gamedatas.factions[this.GUARDIAN])}</strong>`, 200);
            }
        },
        
        moveToken: function(id,token_location) {
            if (id == this.EMPIRE_TOKEN) {
                var empire_token = $('empire-token');
                dojo.removeClass(empire_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");
                dojo.addClass(empire_token, "slot-" + token_location);
            }
            if (id == this.TRIBES_TOKEN) {
                var tribes_token = $('tribes-token');
                dojo.removeClass(tribes_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");
                dojo.addClass(tribes_token, "slot-" + token_location);
            }
            if (id == this.GUARDIAN_TOKEN) {
                var guardian_token = $('guardian-token');
                dojo.removeClass(guardian_token, "slot-1 slot-2 slot-3 slot-4 slot-5 slot-6 slot-7 slot-8 slot-9 slot-10 slot-11 slot-12 slot-13");
                dojo.addClass(guardian_token, "slot-" + token_location);
            }
        },
        
        checkTokenLimit($token_mvt, $token_pos) {
            var $new_token_pos = $token_pos + $token_mvt;
    
            if($new_token_pos > 12) $token_mvt = $token_mvt - ($new_token_pos - 12);
            if($new_token_pos < 1) $token_mvt = $token_mvt + (1 - $new_token_pos);
    
            return $token_mvt;
        },

        addPrimaryActionButton(id, text, callback, zone = 'customActions') {
            if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'blue');
        },
      
        addSecondaryActionButton(id, text, callback, zone = 'customActions') {
            if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'gray');
        },

        addDangerActionButton(id, text, callback, zone = 'customActions') {
            if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'red');
        },

        addImageActionButton(id, div_html, callback, name = '', zone = 'customActions') { // div_html is string not node
            this.addActionButton(id, div_html, callback, zone, false, 'gray'); 

            if(name) this.addTooltipHtml(id, _(name), 200);

            //dojo.style(id, "border", "none"); // remove ugly border
            //dojo.addClass(id, "bgaimagebutton"); // add css class to do more styling
        },
        
        addEmpireTokenButton(id, empire_mvt, callback) {
            if (!$(id)) this.addImageActionButton(id, `<span class="span-value">${empire_mvt}</span> <div id="empire-token-log" class="card-icon"></div>`, callback);
        },
        
        addTribesTokenButton(id, tribes_mvt, callback) {
            if (!$(id)) this.addImageActionButton(id, `<span class="span-value">${tribes_mvt}</span> <div id="tribes-token-log" class="card-icon"></div>`, callback);
        },
        
        addBothTokenButton(empire_mvt, tribes_mvt, callback) {
            $html = ``;
            if(empire_mvt != 0) $html += `<span class="span-value">${empire_mvt}</span> <div id="empire-token-log" class="card-icon"></div>`;
            if(tribes_mvt != 0) $html += `<span class="span-value">${tribes_mvt}</span> <div id="tribes-token-log" class="card-icon"></div>`;

            this.addImageActionButton(`both_token`, $html, callback);
        },
        
        addDiscardButton(callback) {
            this.addImageActionButton('discard_button',`<div class='card-icon icon icon-${this.WILDERNESS}'></div>`, callback);
        },

        addCancelButton(name, handler) {
            if (!name) name = _('Cancel');
            if (!handler) handler = () => this.cancelLocalStateEffects();
            if ($('button_cancel')) dojo.destroy('button_cancel');
            this.addActionButton('button_cancel', name, handler, 'customActions', false, 'gray');
        },
    
        clearActionButtons() {
            dojo.empty('customActions');
        },

        takeAction(action, args, handler) {
            if (!args) args = {};
                
            args.lock = true;
            //if (this.checkAction(action)) {
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", args,
                this, (result) => { },
                handler);
            //}
        },
        takeActionNoLock(action, args, handler) {
            if (!args) args = {};
                
            //if (this.checkAction(action)) {
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", args,
                this, (result) => { },
                handler);
            //}
        },

        resetPageTitle() {
            this.changePageTitle();
          },
      
        changePageTitle(suffix = null) {
            console.log(suffix);
            if (!this.gamedatas.gamestate['descriptionmyturn' + suffix]) return;
        
            this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate['descriptionmyturn' + suffix];
            if (this.gamedatas.gamestate['description' + suffix])
                this.gamedatas.gamestate.description = this.gamedatas.gamestate['description' + suffix];

            this.updatePageTitle();
        },
      
        changePageTitleCardEffect(description) {
            if (description == null) return;
            this.gamedatas.gamestate.descriptionmyturn = description;
            this.updatePageTitle();
        },

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            this.stateManager.onEnteringState(stateName, args);
        },
        
        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            this.stateManager.onLeavingState(stateName);          
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            this.stateManager.onUpdateActionButtons(stateName, args);
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        // addCorruptionToken(token, cardId) {
        //     dojo.place(this.getCorruptionTokenDiv(token.id, token.type, 'onHeroCard'), document.getElementById(`herocard-${cardId}`));
        //     // const side = document.getElementById(`herocard-${cardId}`).dataset.side;

        //     // document.getElementById(`herocard-${cardId}-${side}`).insertAdjacentHTML(
        //     //     "afterbegin",
        //     //     `<div style="text-align:initial;"><span class='card-icon icons-corruption icon-${tokenType} onHeroCard'></span></div>`,
        //     // );
        // },
        // getCorruptionTokenDiv(tokenId, type, onHeroCard = null) {
        //     return `<div id="corruptionToken_${tokenId}" data-type="${type}" class="card-icon icons-corruption ${onHeroCard}"></div>`;
        // },
        
        createArtifactToken: function (number) {
            return this.format_block('jstpl_artifact', {number});
        },
        createCorruptionToken: function (tokenId, type, onHeroCard = '') {
            return this.format_block('jstpl_corruption', {tokenId, type, onHeroCard});
        },
        // createPowerSmall: function (powerId, cssClass) {
        //     var power = this.getPower(powerId);
        //     if (cssClass) {
        //     power = Object.assign({}, power); // copy
        //     power.type = (power.type || '') + ' ' + cssClass;
        //     }
        //     return this.format_block('jstpl_powerSmall', power);
        // },
        
        slide: function slide(sourceId, targetId) {
            var _this = this;
            return new Promise(function (resolve, reject) {
            var animation = _this.slideToObject(sourceId, targetId);
            dojo.connect(animation, 'onEnd', resolve);
            animation.play();
            });
        },

        setValueFactionCounter(factionId, playerId, value, isGuardian) {

            switch(Number(factionId)) {
                case 1 :
                    this.undeadCounters[playerId].setValue(value);
                break;
                case 2 :
                    this.waterfolkCounters[playerId].setValue(value);
                break;
                case 3 :
                    this.empireCounters[playerId].setValue(value);
                break;
                case 4 :
                    this.tribesCounters[playerId].setValue(value);
                break;
            }

            if(isGuardian) this.guardianCounters[playerId].incValue(value);
        },

        incValueFactionCounter(factionId, playerId, incValue, isGuardian) {

            switch(Number(factionId)) {
                case 1 :
                    this.undeadCounters[playerId].incValue(incValue);
                break;
                case 2 :
                    this.waterfolkCounters[playerId].incValue(incValue);
                break;
                case 3 :
                    this.empireCounters[playerId].incValue(incValue);
                break;
                case 4 :
                    this.tribesCounters[playerId].incValue(incValue);
                break;
                case 10 :
                    this.undeadCounters[playerId].incValue(incValue);
                    this.waterfolkCounters[playerId].incValue(incValue);
                    this.empireCounters[playerId].incValue(incValue);
                    this.tribesCounters[playerId].incValue(incValue);
                break;
            }
            
            if(isGuardian) this.guardianCounters[playerId].incValue(incValue);
        },

        // incValueGuardianCounter(playerId, incValue) {
        //     this.guardianCounters[playerId].incValue(incValue);
        // },

        disablePlayerScore(player_ids) {
            Object.values(this.gamedatas.players).forEach(player => {
                if(!player_ids.includes(Number(player.id))) {
                    var collection = document.getElementsByClassName(`status-score-${player.id}`);

                    for (let i = 0; i < collection.length; i++) {
                        //collection[i].classList.remove('highlight-card');
                        collection[i].style.transition = 'opacity 2s';
                        collection[i].style.opacity = 0.2;
                    }
                }
            });
        },

        
        slide: function slide(sourceId, targetId) {
            var _this = this;
            return new Promise(function (resolve, reject) {
            var animation = _this.slideToObject(sourceId, targetId);
            dojo.connect(animation, 'onEnd', resolve);
            animation.play();
            });
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your hiddenleaders.game.php file.
        
        */
       
        setupNotifications: function()
        { 
            const ANIMATION_MS = 500;
            const SCORE_MS = 800;

            //dojo.connect(this.notifqueue, 'addToLog', () => this.addLogClass());

            const notifs = [
                ['skip', ANIMATION_MS],
                ['playCard', ANIMATION_MS],
                ['discard', ANIMATION_MS],
                ['drawCard', 200],
                ['drawCardFromTavern', 200],
                ['fillTavern', ANIMATION_MS],
                ['moveToken', ANIMATION_MS],
                //['score', ANIMATION_MS],
                ['place', ANIMATION_MS],
                ['pick', ANIMATION_MS],
                ['bury', ANIMATION_MS],
                ['flip', ANIMATION_MS],
                ['exchange', ANIMATION_MS],
                ['look', ANIMATION_MS],
                ['noAction', ANIMATION_MS],
                ['reveal', ANIMATION_MS],
                ['cardInPlay', ANIMATION_MS],
                ['handToCardInPick', ANIMATION_MS],
                //['cardInPick', ANIMATION_MS],
                ['replaceCardInPick', ANIMATION_MS],
                ['noFaction', ANIMATION_MS],
                ['endWinningFaction', SCORE_MS],
                ['scoreAlignedLeaders', SCORE_MS],
                ['endAlignedLeaders', SCORE_MS],
                ['scoreMostHeroesFaction', SCORE_MS],
                ['endMostHeroesFaction', SCORE_MS],
                ['scoreLowestTotalHeroes', SCORE_MS],
                ['endLowestTotalHeroes', SCORE_MS],
                ['scoreLeaderNumber', SCORE_MS],
                ['endLeaderNumber', SCORE_MS],
                ['drawFate', 200],
                ['drawCorruptionToken', SCORE_MS],
                ['moveCorruptionToken', SCORE_MS],
                ['selectArtifact', SCORE_MS],
                ['playArtifact', SCORE_MS],
            ];

            notifs.forEach((notif) => {
                dojo.subscribe(notif[0], this, `notif_${notif[0]}`);
                this.notifqueue.setSynchronous(notif[0], notif[1]);
            });

            this.notifqueue.setIgnoreNotificationCheck('discard', (notif) => 
                notif.args.playerId == this.player_id && notif.args.nbCards
            );
            this.notifqueue.setIgnoreNotificationCheck('drawCard', (notif) => 
                notif.args.playerId == this.player_id && !notif.args.flagNotif
            );
            this.notifqueue.setIgnoreNotificationCheck('drawCardNotif', (notif) => 
                notif.args.playerId == this.player_id && notif.args.player_name
            );
            this.notifqueue.setIgnoreNotificationCheck('place', (notif) => 
                (notif.args.playerId == this.player_id || notif.args.targetId == this.player_id) && !notif.args.card_name
            );
            this.notifqueue.setIgnoreNotificationCheck('pick', (notif) => 
                (notif.args.playerId == this.player_id || notif.args.targetId == this.player_id) && !notif.args.card_name
            );
            this.notifqueue.setIgnoreNotificationCheck('exchange', (notif) => 
                (notif.args.playerId == this.player_id || notif.args.targetId == this.player_id) && !notif.args.card_1_name
            );
            this.notifqueue.setIgnoreNotificationCheck('handToCardInPick', (notif) => 
                (notif.args.playerId == this.player_id || notif.args.currentPlayer == this.player_id) && !notif.args.card_name
            );
            // this.notifqueue.setIgnoreNotificationCheck('drawCorruptionToken', (notif) => 
            //     notif.args.playerId == this.player_id && notif.args.corruption
            // );
            this.notifqueue.setIgnoreNotificationCheck('moveCorruptionToken', (notif) => 
                (notif.args.playerId == this.player_id && !notif.args.corruption) || (notif.args.targetId == this.player_id && !notif.args.card_name)
            );
        },  

        notif_skip:function(notif) {},
        
        notif_playCard: function(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var card = args.card;

            //this.isHorizontal() ? player_table.playerVisibleCards.addCard(card) : player_table.playerVisibleCards[card.type].addCard(card);
            //player_table.playerVisibleCards[card.type].addCard(card);

            card.isYellow ? player_table.playerVisibleCards[this.YELLOW].addCard(card) :  player_table.playerVisibleCards[card.type].addCard(card);

            this.visibleCounters[playerId].incValue(1);
            
            this.incValueFactionCounter(card.type, playerId, 1, card.isGuardian);
        },

        async notif_discard(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var playerTable = this.getPlayerTable(playerId);
            var cards = args.cards;
            var from = args.from_value;
            
            for (var card of cards) {
                card = {id: card.id};
                
                this.decks.discard_cards.addCard(card);

                //if(from == this.HAND) this.handCounters[playerId].incValue(-1);
            };

            //this.decks.discard_cards.setCardNumber(args.remainingCards, args.topCard);
        },

        refillDeck(card, remainingCards) {
            this.decks.deck.addCard(card);
            this.decks.deck.setCardNumber(remainingCards, card);
            this.decks.discard_cards.setCardNumber(0, null);
            this.decks.deck.shuffle();
        },

        notif_drawCard(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var card = args.card;
            var from = args.from_value;
            
            var cardInPick = args.cardInPick;

            if (this.isCurrentPlayerActive() && cardInPick) document.getElementById('card-pick').dataset.visible = true;

            card = this.isCurrentPlayerActive() ? card : {id: card.id};
            
            if(from == this.HARBOR && this.decks.deck.getCardNumber() == 0) this.refillDeck(card, args.remainingCards);
            
            !cardInPick ? player_table.playerHand.addCard(card) : this.decks.pick_cards.addCard(card);

            switch(from) {
                case this.HARBOR:
                    this.decks.deck.setCardNumber(args.remainingCards, args.topCard);
                break;
                case this.WILDERNESS:
                    this.decks.discard_cards.setCardNumber(args.remainingCards, args.topCard);
                break;
                case this.GRAVEYARD:
                    this.decks.graveyard_cards.setCardNumber(args.remainingCards, args.topCard);
                break;
            }
        },

        // async notif_drawCard(notif) {
        //     var args = notif.args;

        //     var playerId = args.playerId;
        //     var player_table = this.getPlayerTable(playerId);
        //     var cards = args.cards;
        //     var from = args.from_value;
            
        //     if(cards.length == 2) var topFirstCard = cards[1];
        //     var i = 1;

        //     for (var card of cards) {
        //         card = this.isCurrentPlayerActive() ? card : {id: card.id};
                    
        //         if(this.decks.deck.getCardNumber() == 0) await this.refillDeck(card, args.remainingCards);
                
        //         player_table.playerHand.addCard(card);

        //         switch(from) {
        //             case   this.HARBOR:
        //                 this.decks.deck.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //             case   this.WILDERNESS:
        //                 this.decks.discard_cards.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //             case   this.GRAVEYARD:
        //                 this.decks.graveyard_cards.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //         }
        //         i++;
        //     };
        // },

        notif_drawCardFromTavern: function(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var card = args.card;

            card = this.isCurrentPlayerActive() ? card : {id: card.id};

            player_table.playerHand.addCard(card);
        },

        notif_fillTavern(notif) {
            var args = notif.args;

            var card = args.card;
            
            if(this.decks.deck.getCardNumber() == 0) this.refillDeck(card, args.remainingCards);
            
            this.decks.tavern_cards.addCard(card);

            if( args.from_value == this.HARBOR) this.decks.deck.setCardNumber(args.remainingCards, args.topCard);
            else if( args.from_value == this.GRAVEYARD) this.decks.graveyard_cards.setCardNumber(args.remainingCards, args.topCard);
        },

        notif_place: function(notif) {
            var args = notif.args;

            var card = args.card;
            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var targetId = args.targetId;
            var target_table = this.getPlayerTable(targetId);
            var from = args.from_value;
            var to = args.to_value;
            
            switch(to) {
                case   this.HIDDEN:
                    card = playerId == this.player_id ? card : { id: card.id, location : 'hiddenCards', location_arg : playerId};
                    player_table.playerHiddenCards.addCard(card);
                    this.hiddenCounters[playerId].incValue(1);
                break;
                case   this.HAND:
                    player_table.playerHand.addCard(card);
                break;
                case   this.WILDERNESS:
                    this.decks.discard_cards.addCard({id: card.id});
                    //this.decks.discard_cards.setCardNumber(args.remainingCards, args.topCard);
                break;
                case   this.VISIBLE:
                    //TO DO CHANGE VISIBLE LAYOUT
                    //this.isHorizontal() ? player_table.playerVisibleCards.addCard(card) : player_table.playerVisibleCards[card.type].addCard(card);
                    //player_table.playerVisibleCards[card.type].addCard(card);
                    card.isYellow ? player_table.playerVisibleCards[this.YELLOW].addCard(card) :  player_table.playerVisibleCards[card.type].addCard(card);

                    this.visibleCounters[playerId].incValue(1);
                    this.incValueFactionCounter(card.type, playerId, 1, card.isGuardian);
                break;
            }

            switch(from) {
                case   this.HAND:
                    //this.handCounters[playerId].incValue(-1);
                break;
                case   this.VISIBLE:
                    this.visibleCounters[targetId].incValue(-1);
                    this.incValueFactionCounter(card.type, targetId, -1, card.isGuardian);
                break;
                case   this.HARBOR:
                    this.decks.deck.setCardNumber(args.remainingCards, args.topCard);
                break;
                case   this.WILDERNESS:
                    this.decks.discard_cards.setCardNumber(args.remainingCards, args.topCard);
                break;
                case   this.GRAVEYARD:
                    this.decks.graveyard_cards.setCardNumber(args.remainingCards, args.topCard);
                break;
            }

            document.getElementById('card-pick').dataset.visible = false;
        },

        notif_pick: function(notif) {
            var args = notif.args;

            var card = args.card;
            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var targetId = args.targetId;
            var target_table = this.getPlayerTable(targetId);
            var from = args.from_value;
            var to = args.to_value;
            
            switch(from) {
                case   this.HAND:
                    //this.handCounters[targetId].incValue(-1);
                break;
                case   this.VISIBLE:
                    this.visibleCounters[targetId].incValue(-1);
                    this.incValueFactionCounter(card.type, targetId, -1, card.isGuardian);
                break;
                case   this.HIDDEN:
                    this.hiddenCounters[targetId].incValue(-1);
                break;
            }

            switch(to) {
                case   this.HAND:
                    card = playerId == this.player_id ? card : { id: card.id, location : `hand${playerId}`};

                    player_table.playerHand.addCard(card);
                    //this.handCounters[playerId].incValue(1);
                break;
                case   this.HIDDEN:
                    card = playerId == this.player_id ? card : { id: card.id, location : 'hiddenCards', location_arg : playerId};
                    player_table.playerHiddenCards.addCard(card);
                    this.hiddenCounters[playerId].incValue(1);
                break;
            }
        },

        notif_bury: function(notif) {
            var args = notif.args;

            var card = args.card;
            var targetId = args.targetId;
            var from = args.from_value;


            if(from ==   this.VISIBLE) {
                this.visibleCounters[targetId].incValue(-1);
                this.incValueFactionCounter(card.type, targetId, -1, card.isGuardian);
            }
            else if(from ==   this.HIDDEN) {
                this.hiddenCounters[targetId].incValue(-1);
                
                this.closePlayerHiddenCards();
            }

            this.decks.graveyard_cards.addCard(card);
            //this.decks.graveyard_cards.setCardNumber(args.remainingCards, args.topCard);
        },

        notif_flip: function(notif) {
            var args = notif.args;

            var card = args.card;
            var targetId = args.targetId;
            var target_table = this.getPlayerTable(targetId);

            var from = args.from_value;

            
            if(from ==   this.VISIBLE) {
                this.incValueFactionCounter(card.type, targetId, -1, card.isGuardian);
                card = targetId == this.player_id ? card : { id: card.id, location : 'hiddenCards', location_arg : targetId};

                target_table.playerHiddenCards.addCard(card);

                this.hiddenCounters[targetId].incValue(1);
                
                this.visibleCounters[targetId].incValue(-1);
            }
            else if(from ==   this.HIDDEN) {
                //TO DO CHANGE VISIBLE LAYOUT
                //this.isHorizontal() ? target_table.playerVisibleCards.addCard(card) : target_table.playerVisibleCards[card.type].addCard(card);
                //target_table.playerVisibleCards[card.type].addCard(card);
                card.isYellow ? target_table.playerVisibleCards[this.YELLOW].addCard(card) :  target_table.playerVisibleCards[card.type].addCard(card);

                this.visibleCounters[targetId].incValue(1);
                this.incValueFactionCounter(card.type, targetId, 1, card.isGuardian);

                this.hiddenCounters[targetId].incValue(-1);
            }

            this.closePlayerHiddenCards();
        },

        notif_exchange: function(notif) {
            var args = notif.args;
            
            var card_1 = args.card_1;
            var card_2 = args.card_2;
            
            var playerId = args.playerId;
            var player_table = this.getPlayerTable(playerId);
            var targetId = args.targetId;
            var target_table = this.getPlayerTable(targetId);
            var card_1_location = args.card_1_location_value;
            var card_2_location = args.card_2_location_value;
            
            switch(card_1_location) {
                case   this.HIDDEN:
                    card_2 = targetId == this.player_id ? card_2 : { id: card_2.id, location : 'hiddenCards', location_arg : targetId};
                    target_table.playerHiddenCards.addCard(card_2);

                    this.closePlayerHiddenCards();
                break;
                case   this.VISIBLE:
                    //TO DO CHANGE VISIBLE LAYOUT
                    //this.isHorizontal() ? target_table.playerVisibleCards.addCard(card_2) : target_table.playerVisibleCards[card_2.type].addCard(card_2);
                    //target_table.playerVisibleCards[card_2.type].addCard(card_2);
                    card_2.isYellow ? player_table.playerVisibleCards[this.YELLOW].addCard(card_2) :  player_table.playerVisibleCards[card_2.type].addCard(card_2);

                    this.incValueFactionCounter(card_2.type, playerId, 1, card_2.isGuardian);
                    this.incValueFactionCounter(card_1.type, playerId, -1, card_1.isGuardian);
                break;
                case   this.TAVERN:
                    card_2.location_arg = card_1.location_arg;
                    this.decks.tavern_cards.addCard(card_2);
                break;
                case   this.HAND:
                    card_2 = playerId == this.player_id ? card_2 : { id: card_2.id, location : `hand${playerId}`};
                    
                    player_table.playerHand.addCard(card_2);
                break;
                case   this.WILDERNESS:
                    this.decks.discard_cards.addCard({ id: card_2.id});
                break;
            }
            switch(card_2_location) {
                case   this.TAVERN:
                    card_1.location_arg = card_2.location_arg;
                    this.decks.tavern_cards.addCard(card_1);
                break;
                case   this.HAND:
                    card_1 = playerId == this.player_id ? card_1 : { id: card_1.id, location : `hand${playerId}`};
                    
                    player_table.playerHand.addCard(card_1);
                break;
                case   this.GRAVEYARD:
                    this.decks.graveyard_cards.addCard(card_1);
                break;
                case   this.HIDDEN:
                    card_1 = playerId == this.player_id ? card_1 : { id: card_1.id, location : 'hiddenCards', location_arg : playerId};
                    player_table.playerHiddenCards.addCard(card_1);
                break;
                case   this.VISIBLE:
                    card_1.isYellow ? target_table.playerVisibleCards[this.YELLOW].addCard(card_1) :  target_table.playerVisibleCards[card_1.type].addCard(card_1);

                    this.incValueFactionCounter(card_1.type, targetId, 1, card_1.isGuardian);
                    this.incValueFactionCounter(card_2.type, targetId, -1, card_2.isGuardian);

                    // fungifiedTrollTargetId = card_2.location_arg;
                    // card_1.isYellow ? this.getPlayerTable(fungifiedTrollTargetId).playerVisibleCards[this.YELLOW].addCard(card_1) :  this.getPlayerTable(fungifiedTrollTargetId).playerVisibleCards[card_1.type].addCard(card_1);

                    // this.incValueFactionCounter(card_1.type, fungifiedTrollTargetId, 1, card_1.isGuardian);
                    // this.incValueFactionCounter(card_2.type, fungifiedTrollTargetId, -1, card_2.isGuardian);
                break;
            }

            document.getElementById('card-pick').dataset.visible = false;
        },

        notif_look: function(notif) {},

        notif_moveToken: function(notif) {
            var args = notif.args;

            if(args.empire_pos) this.moveToken( this.EMPIRE_TOKEN, args.empire_pos);
            if(args.tribes_pos) this.moveToken( this.TRIBES_TOKEN, args.tribes_pos);
            
            if(args.guardian_pos) this.moveToken( this.GUARDIAN_TOKEN, args.guardian_pos);

            var el = document.getElementById('player_board_winning_faction_name');

            el.dataset.faction = args.winningFaction_value;
            el.innerHTML = `${args.winningFaction_translated} ${args.winningFaction}`;
        },

        notif_noAction: function(notif) {},

        notif_reveal: function(notif) {
            var args = notif.args;

            var card = args.card;

            this.cardsManager.setCardVisible(card,true);

            var cardDiv = this.cardsManager.getCardElement(card);
            cardDiv.style.zIndex = '20';

            return this.animationManager.play(new BgaCumulatedAnimation({
                animations: [
                    new BgaShowScreenCenterAnimation({ element: cardDiv, transitionTimingFunction: 'ease-in-out' }),
                    new BgaPauseAnimation({}),
                    new BgaAttachWithAnimation({
                        animation: new BgaSlideAnimation({ element : cardDiv, transitionTimingFunction: 'ease-out' }),
                        attachElement: cardDiv.parentElement
                    })
                ]
            })
            ).then( () => cardDiv.style.removeProperty('z-index')
            ).finally( () => this.cardsManager.setCardVisible({id : card.id},false));
        },

        notif_cardInPlay(notif) {
            var args = notif.args;
            if(args.cardToMove) this.decks.inPlay_cards.addCard(args.card);
        },

        notif_handToCardInPick(notif) {
            var args = notif.args;
            
            var playerId = args.playerId;
            var currentPlayer = args.currentPlayer;
            var player_table = this.getPlayerTable(playerId);
            var card = args.card;
            var from = args.from_value;
            
            var cardInPick = args.cardInPick;

            if(this.player_id == currentPlayer) document.getElementById('card-pick').dataset.visible = true;

            //card = this.isCurrentPlayerActive() ? card : {id: card.id};
            
            this.decks.pick_cards.addCard(card);
        },


        // notif_cardInPick(notif) {
        //     var args = notif.args;

        //     var cards = args.cards;
        //     var from = args.from_value;

        //     if (this.isCurrentPlayerActive()) document.getElementById('card-pick').dataset.visible = true;
            
        //     if(cards.length == 2) var topFirstCard = cards[1];
        //     var i = 1;

        //     for (var card of cards) {
        //         if(this.decks.deck.getCardNumber() == 0 && from ==   this.HARBOR) this.refillDeck(card, args.remainingCards);
                
        //         this.decks.pick_cards.addCard(card);
                
        //         switch(from) {
        //             case   this.HARBOR:
        //                 this.decks.deck.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //             case   this.WILDERNESS:
        //                 this.decks.discard_cards.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //             case   this.GRAVEYARD:
        //                 this.decks.graveyard_cards.setCardNumber(args.remainingCards, i == 1 && topFirstCard ? topFirstCard: args.topCard);
        //             break;
        //         }
        //         i++;
        //     };
        // },

        notif_replaceCardInPick(notif) {
            var args = notif.args;
            var cards = args.cards;
            console.log(args);
            if(args.graveyard) this.decks.graveyard_cards.addCards(cards);
            if(args.wilderness) {
                this.decks.discard_cards.addCards(Object.values(cards));
                this.decks.discard_cards.shuffle();
            }

            if(args.targetId) {
                if(args.targetId == this.player_id) this.getPlayerTable(args.targetId).playerHand.addCards(cards);
                else {
                    for (var card of cards) {
                        card = {id: card.id};
                        this.getPlayerTable(args.targetId).playerHand.addCard(card);
                    };
                }
            }

            if(args.player_name && this.decks.fate_cards) {
                this.decks.fate_cards.addCards(cards);
                this.decks.fate_cards.setCardNumber(6, args.topCard);
                this.decks.fate_cards.shuffle();
            }

            document.getElementById('card-pick').dataset.visible = false;
        },

        notif_drawFate(notif) {
            var args = notif.args;
            var card = args.card;

            this.decks.fatePick_cards.addCard(card);
            this.decks.fate_cards.setCardNumber(args.remainingCards, args.topCard);
        },

        notif_drawCorruptionToken(notif) {
            var args = notif.args;
            var token = args.token;
            var playerId = args.playerId;
            console.log(args);
            document.getElementById('card-pick').dataset.visible = true;

            // document.getElementById('card-pick').insertAdjacentHTML(
            //     "afterbegin",
            //     `<span class='card-icon icons-corruption icon-${token.type_arg}'></span>`,
            // );

            
            //var cardDiv = `<span class='card-icon icons-corruption icon-${token.type_arg}'></span>`;
            //cardDiv.style.zIndex = '20';

            if (args.onCorruptionCardId) {
                tokenDiv = document.getElementById(`corruptionToken_${args.onCorruptionCardId}`);
                dojo.removeClass(tokenDiv);
                dojo.addClass(tokenDiv, `card-icon icons-corruption`);
                tokenDiv.dataset.type = token.type;
            }
            else {
                //dojo.place(this.format_block('jstpl_corruption', {id: token.id, type_arg:token.type_arg} ), 'central_area');
                tokenDiv = dojo.place(this.createCorruptionToken(token.id, token.type), 'player_board_winning_faction');
                //this.slideToObject('card_' + card_id, slot).play();
            }
            
            console.log(tokenDiv);
            
            // this.attachToNewParent(tokenDiv, 'card-pick');
            // this.slideToObject(tokenDiv, 'card-pick').play();

            //dojo.addClass(tokenDiv, "slot-" + this.gamedatas.empireToken);
           
            this.slide(tokenDiv,document.getElementById(`card-pick`));
            // return this.animationManager.play(new BgaCumulatedAnimation({
            //     animations: [
            //         new BgaAttachWithAnimation({
            //             animation: new BgaSlideAnimation({ element : tokenDiv, transitionTimingFunction: 'ease-out' }),
            //             attachElement: document.getElementById('card-pick')
            //         })
            //     ]
            // }));
            
        },
        notif_moveCorruptionToken(notif) {
            var args = notif.args;
            console.log(args);

            var token = args.token;
            var playerId = args.playerId;
            var card_id = args.card.id;

            if(playerId == this.player_id) {
                tokenDiv = document.getElementById(`corruptionToken_${token.id}`);
                dojo.removeClass(tokenDiv);
                dojo.addClass(tokenDiv, `card-icon icons-corruption onHeroCard`);
                tokenDiv.dataset.type = token.type;
            }
            else if(token.type_arg == this.player_id) {
                tokenDiv = dojo.place(this.createCorruptionToken(token.id, token.type, 'onHeroCard'), 'player_board_winning_faction');
            }
            else {
                tokenDiv = dojo.place(this.createCorruptionToken(token.id, null, 'onHeroCard'), 'player_board_winning_faction');
            }
            console.log(tokenDiv);
            this.slide(tokenDiv,document.getElementById(`herocard-${card_id}`));
            // return this.animationManager.play(new BgaCumulatedAnimation({
            //     animations: [
            //         new BgaAttachWithAnimation({
            //             animation: new BgaSlideAnimation({ element : tokenDiv, transitionTimingFunction: 'ease-out' }),
            //             attachElement: document.getElementById(`herocard-${card_id}`)
            //         })
            //     ]
            // }));

            //this.addCorruptionToken(token.location_arg, token.type_arg);
        },
        
        notif_selectArtifact(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var artifact = args.artifact;

            var removedArtifact = this.getPlayerTable(playerId).playerArtifact.getCards().filter(a => a.id != artifact.id)[0];
            this.getPlayerTable(playerId).playerArtifact.removeCard(removedArtifact);
        },

        notif_playArtifact(notif) {
            var args = notif.args;
            var artifact = args.artifact;
            
            var tokenNumber = artifact.uses + 1;
            document.getElementById(`artifactcard-${artifact.id}-front`).querySelector(`[data-number="${tokenNumber}"]`).remove();
        },
        
        notif_noFaction(notif) {},

        notif_endWinningFaction(notif) {
            var args = notif.args;
            
            document.getElementById(`hiddenleaders-score`).scrollIntoView({ behavior: "smooth", block: "center" });

            const cell = document.getElementById(`winning-faction`);
            cell.innerHTML = `${args.winningFaction_translated} ${args.winningFaction}`;
            cell.classList.add('highlight');
        },

        notif_scoreAlignedLeaders(notif) {
            var args = notif.args;
            
            var playerId = args.playerId;
            var leaderId = args.leaderId;

            const cell = document.getElementById(`row-leader-${playerId}`);
            cell.dataset.leaderId = leaderId;
            cell.classList.add('highlight-card');

            cell.onanimationend = () => {
                cell.classList.remove('highlight-card');
            };
        },

        notif_endAlignedLeaders(notif) {
            var args = notif.args;
            var aligned_player_ids = args.aligned_player_ids;

            this.disablePlayerScore(aligned_player_ids);
            
            if(args.winner_id) this.scoreCtrl[args.winner_id].toValue(args.nbCardsFaction == 0 ? 1 : args.nbCardsFaction);
        },
        
        notif_scoreMostHeroesFaction(notif) {
            var args = notif.args;
            
            var playerId = args.playerId;
            var winningFaction = args.winningFaction;
            var nbCardsFaction = args.nbCardsFaction;

            dojo.removeClass(document.getElementById('score-row-hero-faction'),'hide-score');

            this.scoreCtrl[playerId].toValue(nbCardsFaction);
            
            this.setValueFactionCounter(winningFaction, playerId, nbCardsFaction, winningFaction == this.GUARDIAN);

            const cell = document.getElementById(`row-hero-faction-${playerId}`);
            cell.innerHTML = `${nbCardsFaction}`;
            cell.classList.add('highlight');
        },

        notif_endMostHeroesFaction(notif) {
            var args = notif.args;
            var player_ids = args.mostHeroesFaction;

            this.disablePlayerScore(player_ids);
        },

        notif_scoreLowestTotalHeroes(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var totalHeroes = args.totalHeroes;

            dojo.removeClass(document.getElementById('score-row-hero-total'),'hide-score');

            const cell = document.getElementById(`row-hero-total-${playerId}`);
            cell.innerHTML = `${totalHeroes}`;
            cell.classList.add('highlight');
        },

        notif_endLowestTotalHeroes(notif) {
            var args = notif.args;
            var player_ids = args.lowestTotalHeroes;
            
            this.disablePlayerScore(player_ids);
        },

        notif_scoreLeaderNumber(notif) {
            var args = notif.args;

            var playerId = args.playerId;
            var leaderValue = args.leaderValue;

            dojo.removeClass(document.getElementById('score-row-leader-value'),'hide-score');

            const cell = document.getElementById(`row-leader-value-${playerId}`);
            cell.innerHTML = `${leaderValue}`;
            cell.classList.add('highlight');
        },

        notif_endLeaderNumber(notif) {
            var args = notif.args;
            var winner_id = args.winner_id;

            this.disablePlayerScore([winner_id]);
        },
        // notif_tableWindow(notif) {
        //     if(typeof notif.args.title == 'object'){
        //         notif.args.title = this.format_string_recursive(_(notif.args.title.log), notif.args.title.args);
        //     }
        //     this.inherited(arguments);
        // },

        /* This enable to inject translatable styled things to logs or action bar */
        /* @Override */
        format_string_recursive(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;

                    if (args.card_name !== null && args.card_name !== undefined && args.card_faction_icon !== null && args.card_faction_icon !== undefined) {
                        args.card_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span> <span class='card-icon faction faction-${faction}'></span>", {
                            name: _(args.card_name),
                            faction: args.card_faction_icon,
                        });
                    }
                    if (args.card_1_name !== null && args.card_1_name !== undefined) {
                        args.card_1_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span> <span class='card-icon faction faction-${faction}'></span>", {
                            name: _(args.card_1_name),
                            faction: args.card_faction_icon_1,
                        });
                    }
                    if (args.card_2_name !== null && args.card_2_name !== undefined) {
                        args.card_2_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span> <span class='card-icon faction faction-${faction}'></span>", {
                            name: _(args.card_2_name),
                            faction: args.card_faction_icon_2,
                        });
                    }

                    if (args.card_names !== null && args.card_names !== undefined) {
                        var cards = [];
                        args.card_names.forEach(card_name => {
                            cards.push(dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span> <span class='card-icon faction faction-${faction}'></span>", {
                                name: _(card_name),
                                faction: args.card_faction_icons[card_name],
                            }))
                        });
                        args.card_names = cards.join(', ');
                    }
                    
                    // if (args.faction_name !== null && args.faction_name !== undefined) {
                    //     switch (args.faction_name) {
                    //         case 'undead':
                    //             args.faction_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span><span class='card-icon faction faction-${faction}'></span>", {
                    //                 name: _(args.faction_name),
                    //                 faction:1
                    //             });
                    //             break;
                    //         case 'water_folk':
                    //             args.faction_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span><span class='card-icon faction faction-${faction}'></span>", {
                    //                 name: _(args.faction_name),
                    //                 faction:2
                    //             });
                    //             break;
                    //         case 'empire':
                    //             args.faction_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span><span class='card-icon faction faction-${faction}'></span>", {
                    //                 name: _(args.faction_name),
                    //                 faction:3
                    //             });
                    //             break;
                    //         case 'tribes':
                    //             args.faction_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span><span class='card-icon faction faction-${faction}'></span>", {
                    //                 name: _(args.faction_name),
                    //                 faction:4
                    //             });
                    //             break;
                    //         case 'guardian':
                    //             args.faction_name = dojo.string.substitute("<span class='card-name faction-${faction}'>${name}</span><span class='card-icon faction faction-${faction}'></span>", {
                    //                 name: _(args.faction_name),
                    //                 faction:5
                    //             });
                    //           break;
                    //       }      
                    // }


                    ['undead', 'water_folk', 'empire', 'tribes', 'guardian', 'faction_name', 'winningFaction'].forEach(field => {
                        if (args[field] !== null && args[field] !== undefined) {
                            args[field] = dojo.string.substitute("<span class='card-icon faction faction-${faction}'></span>", {
                                faction: args[field],
                            });
                        }
                    });

                    ['hand', 'from', 'to', 'visible', 'hidden', 'wilderness', 'graveyard', 'harbor', 'tavern', 'player', 'artifactToken', 'slow', 'fast'].forEach(field => {
                        if (args[field] !== null && args[field] !== undefined) {
                            if(args[field] == this.HAND) args[field] = dojo.string.substitute(_("hand"));
                            else {
                                args[field] = dojo.string.substitute("<span class='card-icon icon icon-${icon}'></span>", {
                                    icon: args[field],
                                });
                            }   
                        }
                    });

                    ['corruption', 'x2', 'corruption-empire', 'corruption-tribes', 'corruption-undead', 'corruption-water_folk', 'corruption-guardian'].forEach(field => {
                        if (args[field] !== null && args[field] !== undefined) {
                            args[field] = dojo.string.substitute("<span data-type='${icon}' class='card-icon icons-corruption'></span>", {
                                icon: args[field],
                            });
                        }
                    });

                    ['empire_token', 'tribes_token', 'guardian_token'].forEach(field => {
                        if (args[field] !== null && args[field] !== undefined) {
                            args[field] = dojo.string.substitute("<div id='${token}' class='card-icon'></div>", {
                                token: args[field],
                            });
                        }
                    });

                    if (args.target_id !== null && args.target_id !== undefined) {
                        args.target_id = dojo.string.substitute("<span style='color:#${player_color};font-weight: 700;'>${player_name}</span>", {
                                player_name: this.getPlayerName(args.target_id),
                                player_color: this.getPlayerColor(args.target_id),
                        });
                    }

                    if (args.player_ids !== null && args.player_ids !== undefined) {
                        var players = [];
                        args.player_ids.forEach(player_id => {
                            players.push(dojo.string.substitute("<span style='color:#${player_color};font-weight: 700;'>${player_name}</span>", {
                                player_name: this.getPlayerName(player_id),
                                player_color: this.getPlayerColor(player_id),
                            }))
                        });
                        args.player_ids = players.join(', ');
                    }
                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        }
   });             
});
