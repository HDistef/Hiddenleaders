class DrawCardStates {
    player_table;
    decks;
    game;
 
    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
       if (!this.game.isCurrentPlayerActive()) return;
       
    }
 
    onLeavingState() {
       dojo.empty('customActions');

       this.game.makeDeckSelectable(false);
       document.getElementById('deck').onclick = null;
       this.game.makeTavernSelectable(false);
       this.decks.tavern_cards.onCardClick = null;
    }
 
    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      
      this.player_table = this.game.getCurrentPlayerTable();

      this.decks = this.game.decks;

      this.game.makeDeckSelectable(true);
      this.game.makeTavernSelectable(true);

      const drawCardFromDeckConfirm = () => {
         this.game.makeDeckSelectable(false);
         document.getElementById('deck').onclick = null;
         this.game.makeTavernSelectable(false);
         this.decks.tavern_cards.onCardClick = null;

         this.game.takeAction("drawCard", {
            location: 'deck'
         });
      };

      const drawAllFromDeckConfirm = () => {
         this.game.makeDeckSelectable(false);
         document.getElementById('deck').onclick = null;
         this.game.makeTavernSelectable(false);
         this.decks.tavern_cards.onCardClick = null;
         
         //for (let i = 0; i < args.nbCards_value; i++) {
         this.game.takeAction("drawCard", {
            location: 'deck',
            nb_cards: args.nbCards_value
         });
         //}
      };

      document.getElementById('deck').onclick = drawCardFromDeckConfirm;
      
      //this.game.addPrimaryActionButton(`drawCardFromDeck_button`, _("Draw all from Harbor"), drawCardFromDeckConfirm);
      this.game.addImageActionButton('drawCardFromDeck_button',`<span style="font-size: large;vertical-align: middle;">Draw all from</span><div class='card-icon icon icon-${  this.game.HARBOR}'></div>`, drawAllFromDeckConfirm);

      const drawCardFromTavernConfirm = (card) => {
         if(card === null) return;
         this.game.makeTavernSelectable(false);
         this.decks.tavern_cards.onCardClick = null;
         
         this.game.takeAction("drawCardFromTavern", {
            id: card.id
         });
      };
      this.decks.tavern_cards.onCardClick = card => drawCardFromTavernConfirm(card);
    }
 
    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }
 