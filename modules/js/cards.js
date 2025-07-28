define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
    return declare('hiddenleaders.cards', null, {
      constructor() {
        this._selectableCards = [];
    },

        setupCardManager() {
            
            /* init CardManager */
            // create the card manager
            this.cardsManager = new CardManager(this, {
                cardHeight: 236,
                cardWidth: 169,
                getId: (card) => `herocard-${card.id}`,
                setupDiv: (card, div) => {
                    div.dataset.cardId = '' + card.id;
                    //div.classList.add("herocard-animation");
                },
                setupBackDiv: (card, div) => {
                    div.classList.add("herocard-back");
                },
                setupFrontDiv: (card, div) => {
                    div.dataset.faction = '' + card.type;
                    div.dataset.number = '' + card.type_arg;
                    div.classList.add("herocard-front");

                    if (div.childNodes.length == 1 && card.type_arg) {
                        this.addTooltipHtml(div.id, this.getCardTooltip(card), 200);

                        // div.insertAdjacentHTML(
                        //     "afterbegin",
                        //     `<div class="wg-card-gametext">
                        //         <div class="wg-card-gametext-title">${_(card.name)}</div>
                               
                        //         <div class="wg-card-gametext-text">${_(card.description)}</div>
                        //     </div>`,
                        // );
                            // <div class="wg-card-gametext-divider"></div>

                        // const helpMarkerId = `herocard-${card.id}-help-marker`;
                        // div.insertAdjacentHTML(
                        //     "afterbegin",
                        //     `<div id="${helpMarkerId}" class="help-marker">
                        //     <i class="fa fa-search" style="color: white"></i>
                        //     </div>`,
                        // );
                        // document.getElementById(helpMarkerId).addEventListener("click", (evt) => {
                        //     evt.stopPropagation();
                        //     evt.preventDefault();
                        //     this.modal.display(card);
                        // });
                    }
                },
                isCardVisible: card => card.type_arg,
                animationManager: this.animationManager,
            });

            this.fateCardsManager = new CardManager(this, {
                cardHeight: 236,
                cardWidth: 169,
                getId: (card) => `fatecard-${card.id}`,
                // setupDiv: (card, div) => {
                //     div.dataset.cardId = '' + card.id;
                // },
                setupBackDiv: (card, div) => {
                    div.classList.add("fatecard-back");
                    html = `<div class="cardToolTip" style="font-size:large">Whenever a Guardian Hero is placed into the Graveyard, the player who performed this move does the following:
                    <br>1 Draw 2 Guardian Fate cards.
                    <br>2 Choose 1 of the cards, reveal the chosen card and perform it.
                    <br>3 Then shuffle the 6 Guardian Fate cards again.</div>`;
                    this.addTooltipHtml(div.id, html, 200);
                },
                setupFrontDiv: (card, div) => {
                    div.dataset.number = '' + card.type_arg;
                    div.classList.add("fatecard-front");
                },
                isCardVisible: card => card.type_arg,
            });
            
            this.artifactCardsManager = new CardManager(this, {
                cardHeight: 236,
                cardWidth: 169,
                getId: (card) => `artifactcard-${card.id}`,
                setupFrontDiv: (card, div) => {
                    div.dataset.number = '' + card.id;
                    div.classList.add("artifactcard-front");
                    
                    card.description = this.format_string_recursive(card.description, this.gamedatas.args);
                    this.inherited(arguments);
                    let html = `<div style="font-size:large">
                    <div style="text-align:center"><strong>${_(card.name)}</strong></div>
                    <div class="cardToolTip" style="text-align:center">${_(card.description)}</div>
                    </div>`;
                    this.addTooltipHtml(div.id, html, 200);
                },
                isCardVisible: card => card.id,
            });

            this.corruptionTokensManager = new CardManager(this, {
                cardHeight: 50,
                cardWidth: 50,
                getId: (card) => `corruption-${card.id}`,
                // setupDiv: (card, div) => {
                //     div.dataset.cardId = '' + card.id;
                // },
                
                setupDiv: (card, div) => {
                    div.classList.add("card-icon");
                    div.classList.add("icons-corruption");
                },
                setupBackDiv: (card, div) => {
                    div.classList.add("corruption-back");
                    html = `<div class="cardToolTip" style="font-size:large">Corruption</div>`;
                    this.addTooltipHtml(div.id, html, 200);
                },
                setupFrontDiv: (card, div) => {
                    div.dataset.number = '' + card.type_arg;
                    div.classList.add("corruption-front");
                },
                isCardVisible: card => card.type_arg,
            });
            
            /* end init CardManager */

        },

        getCardTooltip(card) {
            card.description_token = this.format_string_recursive(card.description_token, this.gamedatas.args);
            card.description = this.format_string_recursive(card.description, this.gamedatas.args);
            this.inherited(arguments);

            let html = `<div style="font-size:large"><div style="text-align:center"><strong>`;
            if(card.faction_name) html += `${_(card.faction_name)} : `;
            html += `${_(card.name)}</strong></div>
            <div class="cardToolTip" style="text-align:center">${_(card.description_token)}</div>
            <div class="cardToolTip" style="text-align:center">${_(card.description)}</div>
            </div>`
            //<div class="tooltip_herocard" data-number="'' + ${card.type_arg}"></div>
            return html;
        },
    });
});