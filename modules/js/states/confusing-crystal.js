class ConfusingCrystalStates {
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
      
      this.selected_cards = [];

      this.client_args.selectable_cards.forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        
        if(el) {
          el.classList.toggle('bga-cards_selectable-card', false);
          el.classList.toggle('bga-cards_selected-card', false);
          this.game.cardsManager.setCardVisible({id : card.id},false);
          this.game.cardsManager.getCardStock({id : card_id}).setOpened(false);

          el.onclick = null;
        }
      });
    }
    onUpdateActionButtons(args) {

        if (!this.game.isCurrentPlayerActive()) return;
        this.client_args = args;

        args.selectable_opponents.forEach(target_id => { 
            this.game.addPrimaryActionButton('select_opponent_' + target_id, this.game.getPlayerName(target_id), () => this.selectConfirm(target_id));
            this.game.toggleButton("select_button", false);
        });

        this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => this.selectConfirm());
        this.game.toggleButton("select_button", false);

        this.game.toggleButton("select_button", false);
        this.client_args.selectable_cards.forEach(card_id => {
            const el = document.getElementById(`herocard-${card_id}`); 
            
            el.classList.toggle('bga-cards_selectable-card');
            this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);
            
            el.onclick = () => this.selectCard(el);
        });
    }

    selectCard(cardElement) {
        const el = cardElement;
        
        this.selected_cards.filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(c));

        if (this.selected_cards.find(c => c.dataset.cardId == el.dataset.cardId)) this.unselectCard(el);
        else {
            this.selected_cards.push(el);
            el.classList.add('bga-cards_selected-card');
        }
        
        const toggleButton = this.checkConfirm();
        this.client_args.selectable_opponents.forEach(target_id => this.game.toggleButton('select_opponent_' + target_id, toggleButton));
    }

    checkConfirm() {
      return this.selected_cards.length == 1;
    }
    
    selectConfirm(target_id) {
        if(!this.checkConfirm()) return;

        this.game.takeAction('confusingCrystal', {
            card_id: this.selected_cards[0].dataset.cardId,
            target_id
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