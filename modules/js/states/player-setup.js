class PlayerSetupStates {
   player_table;
   _setup_discard_card = null;
   _setup_playHiddenCard_card = null;
   game;
   
   constructor(game) { this.game = game; }
 
   onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      
      this.player_table = this.game.getCurrentPlayerTable();
      this.decks = this.game.decks;
      
      const hiddenCardConfirm = () => {
         if(this.player_table.playerHand.getSelection().length == 0) return;
         
         this._setup_playHiddenCard_card = this.player_table.playerHand.getSelection()[0];

         this.game.takeActionNoLock("playHiddenCard", {
            id: this._setup_playHiddenCard_card.id,
         });

         dojo.destroy("playHiddenCard_button");
         document.getElementById(`player_table_hidden_wrapper_${this.game.player_id}`).onclick = null;
         this.game.makeHiddenSelectable(this.game.player_id, false);
         
         if(this._setup_discard_card) {
            this.player_table.playerHand.setSelectionMode("none");
            this.player_table.playerHand.onSelectionChange = null;
         }

         this.game.changePageTitle('discard');
      };

      if(!this._setup_playHiddenCard_card) {
         document.getElementById(`player_table_hidden_wrapper_${this.game.player_id}`).onclick = hiddenCardConfirm;
         //this.game.addPrimaryActionButton(`playHiddenCard_button`, _("Face-down"), hiddenCardConfirm);
         this.game.addImageActionButton('playHiddenCard_button',`<div class='card-icon icon icon-${  this.game.HIDDEN}'></div>`, hiddenCardConfirm, 'Play Hidden Card');
         this.game.toggleButton("playHiddenCard_button", false);
      }



      const discardConfirm = () => {
         if(this.player_table.playerHand.getSelection().length === 0) return;

         this._setup_discard_card = this.player_table.playerHand.getSelection()[0];
         
         this.game.takeActionNoLock("discard", {
            ids: this._setup_discard_card.id,
         });

         dojo.destroy("discard_button");
         this.game.makeDiscardSelectable(false);
         document.getElementById('discard').onclick = null;
         
         if(this._setup_playHiddenCard_card) {
            this.player_table.playerHand.setSelectionMode("none");
            this.player_table.playerHand.onSelectionChange = null;
         }

         this.game.changePageTitle('facedown');
      };

      if(!this._setup_discard_card) {
         document.getElementById('discard').onclick = discardConfirm;
         this.game.addDiscardButton(discardConfirm);
         this.game.toggleButton("discard_button", false);
      } 



      const handleChange = () => {
         const card_selected = this.player_table.playerHand.getSelection().length == 1;
         if(this._setup_discard_card == null) {
               this.game.makeDiscardSelectable(card_selected);
               this.game.toggleButton("discard_button", card_selected);
         }
         if(this._setup_playHiddenCard_card == null) {
               this.game.makeHiddenSelectable(this.game.player_id, card_selected);
               this.game.toggleButton("playHiddenCard_button", card_selected);
         }
      };
      
      this.player_table.playerHand.setSelectionMode('single');
      this.player_table.playerHand.onSelectionChange = handleChange;
    }
 
   onLeavingState() {
      dojo.empty('customActions');

      this._setup_discard_card = null;
      this._setup_playHiddenCard_card = null;

      this.player_table.playerHand.setSelectionMode("none");
      this.player_table.playerHand.onSelectionChange = null;
    }
 
   onUpdateActionButtons(args) {
      if(Object.keys(args.discard).length > 0) {
         this._setup_discard_card = args.discard;
         this.game.changePageTitle('facedown');
      }
      if(Object.keys(args.hiddenCard).length > 0) {
         this._setup_playHiddenCard_card = args.hiddenCard;
         this.game.changePageTitle('discard');
      }
    }
 
   restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }
 