class BludgeoningBlowfishStates {
    game;
    client_args;
    selected_cards = [];

    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
 
    onLeavingState() {
      dojo.empty('customActions');
      
      this.client_args.selectable_cards.forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          el.classList.toggle('bga-cards_selectable-card', false);
          el.classList.toggle('bga-cards_selected-card', false);
          el.onclick = null;
      });
    }
 
    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      this.client_args = args;

      const selectCard = e => {
        const el = e.target.parentElement.parentElement;

        this.selected_cards.filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(c));
        
        if (this.selected_cards.find(c => c.dataset.cardId == el.dataset.cardId)) {
          this.unselectCard(el);
        }
        else {
          this.selected_cards.push(el);
          el.classList.add('bga-cards_selected-card');
        }

        if(this.selected_cards.length == 1) {
            this.game.toggleButton("turnover_button", true);
            this.game.toggleButton("look_button", true);
        }
        else {
            this.game.toggleButton("turnover_button", false);
            this.game.toggleButton("look_button", false);
        }
      };
      
      document.getElementById(`herocard-${args.selectable_cards[0]}`).scrollIntoView({ behavior: "smooth", block: "center" });

      args.selectable_cards.forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          el.classList.toggle('bga-cards_selectable-card');
          
          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

          el.onclick = selectCard;
      });
      
      this.game.addPrimaryActionButton(`turnover_button`, _("Turn over selected card"), () => this.selectConfirm('moveCard'));
      this.game.toggleButton("turnover_button", false);
      
      this.game.addPrimaryActionButton(`look_button`, _("Look at selected card"), () => this.selectConfirm('lookCard'));
      this.game.toggleButton("look_button", false);
    }

    selectConfirm(takeAction) {
      if(this.selected_cards.length != 1 ) return;
      
      this.game.takeAction(takeAction, {
        ids: this.selected_cards.map(card => card.dataset.cardId).join(";"),
      });
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