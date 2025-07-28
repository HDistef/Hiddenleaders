class MultiSelectCardStates {
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

      this.client_args.selectable_cards.flat().forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        if(el) {
          el.classList.toggle('bga-cards_selectable-card', false);
          el.classList.toggle('bga-cards_selected-card', false);
          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(false);
          
          el.onclick = null;
        }
      });
    }

    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      this.client_args = args;

      if(args.pick_card) document.getElementById('card-pick').dataset.visible = true;

      this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => this.selectConfirm());
      this.game.toggleButton("select_button", false);
      
      document.getElementById(`herocard-${args.selectable_cards[0][0]}`).scrollIntoView({ behavior: "smooth", block: "center" });

      for (const [key, cards] of Object.entries(args.selectable_cards)) {
        this.selected_cards[key] = [];
        
        // Specific MummyMystic => You may exchange the top card from graveyard with 1 your visible
        if(!Array.isArray(args.selectable_cards[1]) && key == 1) {
          var card_id = args.selectable_cards[1];
          const el = document.getElementById(`herocard-${card_id}`); 
          
          this.selected_cards[key].push(el);

          //this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

          document.getElementById('graveyard_cards').classList.remove('card-stock', 'deck');
          el.classList.add( 'bga-cards_selected-card' );
          
          //this.selected_cards[key] = el;
          continue;
        }
        cards.forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          el.classList.toggle('bga-cards_selectable-card');

          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

          el.onclick = () => this.selectCard(key,el);
        });
      }
      
      if(args.canPass) this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction('nextEffect',{}));
      
      if(args.takeAction == 'discard') this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction("nextEffect", {}));
    }
    
    checkConfirm(arrays) {
      // Specific NightmarishNorthman => Bury 1 of your visible and 1 visible of another player
      if(this.client_args.card_class == 'NightmarishNorthman' && (this.client_args.selectable_cards[0].length == 0 || this.client_args.selectable_cards[1].length == 0)) return this.selected_cards.flat().length == 1;
      
      // Specific GiganticDuo => You may bury any 1 ${visible} ${empire} AND/OR bury any 1 ${visible} {water_folk}
      if(this.client_args.card_class == 'GiganticDuo') return this.selected_cards[0].length == 1 || this.selected_cards[1].length == 1;

      if(this.client_args.canPass) return arrays.every(arr => arr.length === arrays[0].length && arr.length > 0 && arr.length <= this.client_args.nb_cards_to_select);
      return arrays.every(arr => arr.length === arrays[0].length && arr.length == this.client_args.nb_cards_to_select);
    }

    selectCard(key, cardElement) {
      const el = cardElement;
      
      if (this.client_args.nb_cards_to_select == 1) this.selected_cards[key].filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(key, c));

      if (this.selected_cards[key].find(c => c.dataset.cardId == el.dataset.cardId)) {
        this.unselectCard(key, el);
      }
      else {
        this.selected_cards[key].push(el);
        el.classList.add('bga-cards_selected-card');
      }

      var toggleButton = this.checkConfirm(this.selected_cards);

      this.game.toggleButton("select_button", toggleButton);
      //console.log(this.selected_cards.flat().map(card => card.dataset.cardId).join(";"));
    }
    selectConfirm() {
      if(!this.checkConfirm(this.selected_cards)) return;

      // Object.values(this.selected_cards).forEach(array => {
      //   if(array.length == 0 || array.length > this.client_args.nb_cards_to_select) return;
      //   if(!this.client_args.canPass && array.length != this.client_args.nb_cards_to_select) return;
        
      //   // if(this.el_card_2.parentElement.id == 'graveyard_cards') {
      //   //   this.el_card_2.parentElement.classList.add('card-stock', 'deck');
      //   // }
      // });
      if(this.client_args.card_class == 'MummyMystic') {
        //if(this.selected_cards[1][0].parentElement.id == 'graveyard_cards') {
        this.selected_cards[1][0].parentElement.classList.add('card-stock', 'deck');
        //}
      }

      if(this.client_args.takeAction == 'exchange') {
        this.game.takeAction('exchange', {
          card_1_ids: this.selected_cards[0].map(card => card.dataset.cardId).join(";"),
          card_2_ids: this.selected_cards[1].map(card => card.dataset.cardId).join(";")
        });
      }
      else {
        this.game.takeAction(this.client_args.takeAction, {
          ids: this.selected_cards.flat().map(card => card.dataset.cardId).join(";")
          //ids: this.selected_cards.map(card => card.dataset.cardId).join(";"),
          //target_id: el.parentElement.dataset?.playerId
        });
      }
      if(this.client_args.nextEffect) this.game.takeAction('nextEffect', {});
    }
    
    unselectCard(key, element) {
      element === null || element === void 0 ? void 0 : element.classList.remove('bga-cards_selected-card');
      var index = this.selected_cards[key].findIndex(c => c.dataset.cardId == element.dataset.cardId);
      if (index !== -1) {
          this.selected_cards[key].splice(index, 1);
      }
    }

    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }