class PhilantropicPhantomStates {
   game;
   selected_cards = [];

   constructor(game) {
     this.game = game;
   }

   onEnteringState(args) {
     if (!this.game.isCurrentPlayerActive()) return;
   }

   onLeavingState() {
     dojo.empty('customActions');
   }

   onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;

      var player = this.game.gamedatas.players[this.game.getPlayerId()];
      var selectable_cards = [...player.hiddenCards,...player.visibleCards];

      const selectCard = e => {
         const el = e.target.parentElement.parentElement;

         this.selected_cards.filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(c));
         
         if (this.selected_cards.find(c => c.dataset.cardId == el.dataset.cardId)) this.unselectCard(el);
         else {
            this.selected_cards.push(el);
            el.classList.add('bga-cards_selected-card');
         }

         this.game.toggleButton("select_button", this.selected_cards.length == 1);
      };
      
      
      selectable_cards.forEach(card => {
         const el = document.getElementById(`herocard-${card.id}`); 
         el.classList.toggle('bga-cards_selectable-card', true);

         this.game.cardsManager.getCardStock({id : card.id}).setOpened?.(true);

         el.onclick = selectCard;
      });
       
       const selectConfirm = () => {
            if(this.selected_cards.length != 1) return;
         
            this.game.takeActionNoLock("bury", {
               id: this.selected_cards[0].dataset.cardId,
            });
 
            dojo.empty('customActions');
            this.selected_cards = [];
      
            selectable_cards.forEach(card => {
               const el = document.getElementById(`herocard-${card.id}`); 
               el.classList.toggle('bga-cards_selectable-card', false);
               el.classList.toggle('bga-cards_selected-card', false);
               el.onclick = null;
            });
       };

       this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => selectConfirm());
       this.game.toggleButton("select_button", false);
   }

   unselectCard(element) {
     element === null || element === void 0 ? void 0 : element.classList.remove('bga-cards_selected-card');
     var index = this.selected_cards.findIndex(c => c.dataset.cardId == element.dataset.cardId);
     if (index !== -1) {
         this.selected_cards.splice(index, 1);
     }
   }

   restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
   }    
}