class DiscardStates {
   player_table;
   game;

   constructor(game) {
      this.game = game;
   }

   onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
   }

   onLeavingState() {
      dojo.empty('customActions');
      
      this.player_table.playerHand.setSelectionMode("none");
      this.player_table.playerHand.onSelectionChange = null;
         
      this.game.makeDiscardSelectable(false);
      document.getElementById('discard').onclick = null;
   }

   onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      
      this.player_table = this.game.getCurrentPlayerTable();
      
      const handleChange = () => {
         const nb_card_selected = this.player_table.playerHand.getSelection().length;
         const discard = this.player_table.playerHand.getCards().length - nb_card_selected === 3;

         const selectableCards = this.player_table.playerHand.getCards().filter(card => {
            let disabled = false;
            if(!this.player_table.playerHand.getSelection().includes(card)) {
               if(this.player_table.playerHand.getCards().length - nb_card_selected == 3) disabled = true;
            }
            return !disabled;
         });

         this.player_table.playerHand.setSelectableCards(selectableCards);

         this.game.makeDiscardSelectable(discard);
         this.game.toggleButton("discard_button", discard);
      };
      
      this.player_table.playerHand.setSelectionMode('multiple');
      this.player_table.playerHand.onSelectionChange = handleChange;

      const discardConfirm = () => {
         const nb_card_selected = this.player_table.playerHand.getSelection().length;
         const discard = this.player_table.playerHand.getCards().length - nb_card_selected === 3;
         if(!discard) return;

         const selected_card_ids = this.player_table.playerHand.getSelection().map((x) => x.id);
         
         this.game.makeDiscardSelectable(false);
         document.getElementById('discard').onclick = null;
         
         this.game.takeAction("discard", {
            ids: selected_card_ids.join(";"),
         });
      };
      
      document.getElementById('discard').onclick = discardConfirm;
      //this.game.addPrimaryActionButton(`discard_button`, _("Wilderness"), discardConfirm);
      this.game.addDiscardButton(discardConfirm);
      this.game.toggleButton("discard_button", false);
   }

   restoreGameState() {
      return new Promise<boolean>((resolve) => resolve(true));
   }
}
 