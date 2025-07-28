class PlayerActionStates {
   player_table;
   game;
   cardInPlay;
   constructor(game) {
      this.game = game;
   }
 
    onEnteringState(args) {
      this.cardInPlay = args === null ? null : args.card;

      if(this.cardInPlay) this.game.changePageTitle('replay');
      if (!this.game.isCurrentPlayerActive()) return;
      this.player_table = this.game.getCurrentPlayerTable();
      
      //   this.game.HAND   this.game.HANDLE
      const handleChange = () => {
         const nb_card_selected = this.player_table.playerHand.getSelection().length;
         const playCard = nb_card_selected == 1;
         const discard = nb_card_selected >= 1 && nb_card_selected <= 3;
      

         this.game.makeVisibleSelectable(this.game.getPlayerId(), playCard);
         this.game.toggleButton("playVisibleCard_button", playCard);

         if(this.cardInPlay) return;

         const selectableCards = this.player_table.playerHand.getCards().filter(card => {
            let disabled = false;
            if(!this.player_table.playerHand.getSelection().includes(card)) {
               if(nb_card_selected >= 3) disabled = true;
            }
            return !disabled;
         });
         this.player_table.playerHand.setSelectableCards(selectableCards);

         this.game.makeDiscardSelectable(discard);
         this.game.toggleButton("discard_button", discard);
         
      };

      this.player_table.playerHand.onSelectionChange = handleChange;
      this.player_table.playerHand.setSelectionMode('multiple');


      // PLAY CARD
      const playCardConfirm = () => {
         if(this.player_table.playerHand.getSelection().length != 1) return;

         const played_card = this.player_table.playerHand.getSelection()[0];

         if(played_card !== null) {
            this.game.makeVisibleSelectable(this.game.player_id, false);
            document.getElementById(`player_table_visible_wrapper_${this.game.player_id}`).onclick = null;
            
            this.game.takeAction("playCard", {
               id: played_card.id,
            });
         }
      };

      document.getElementById(`player_table_visible_wrapper_${this.game.player_id}`).onclick = playCardConfirm;
      this.game.addImageActionButton('playVisibleCard_button',`<div class='card-icon icon icon-${  this.game.VISIBLE}'></div>`, playCardConfirm, 'Play Visible Card');
      this.game.toggleButton("playVisibleCard_button", false);

      if(this.cardInPlay) {
         if(this.cardInPlay.class == 'DoubtfulPriest') {

            this.player_table.playerHand.setSelectionMode('single');
            this.player_table.playerHand.setSelectableCards(this.player_table.playerHand.getCards().filter(c => c.type != this.game.EMPIRE));
            //return;
         }
      }
      else {
         // DISCARD
         const discardConfirm = () => {
            const nb_card_selected = this.player_table.playerHand.getSelection().length;
            if( nb_card_selected < 1 || nb_card_selected > 3) return;

            const selected_card_ids = this.player_table.playerHand.getSelection().map((x) => x.id);

            if (selected_card_ids.length >= 1 && selected_card_ids.length <= 3) {
               this.game.makeDiscardSelectable(false);
               document.getElementById('discard').onclick = null;

               this.game.takeAction("discard", {
                  ids: selected_card_ids.join(";"),
               });
            }
         };
         document.getElementById('discard').onclick = discardConfirm;
         this.game.addDiscardButton(discardConfirm);
         this.game.toggleButton("discard_button", false);
      }
      
      //PASS
      this.game.addDangerActionButton(`pass_button`, _("Pass"), () => {
         this.game.takeAction("noAction", {});
      });

      //Spread Corruption
      if(args.canSpread) {
         this.game.addDangerActionButton(`corruption_button`, _("Spread Corruption"), () => {
            this.game.takeAction("spreadCorruption", {});
         });
      }
      //Use Artifact
      if(args.artifactAvailable) {
         this.game.addDangerActionButton(`artifact_button`, _(`Play ${this.player_table.playerArtifact.getCards()[0].name}`), () => {
            this.game.takeAction("playArtifact", {});
         });
      }
    }
 
    onLeavingState() {
       dojo.empty('customActions');
       this.game.actionManager.reset();

       this.player_table.playerHand.setSelectionMode("none");
       this.player_table.playerHand.onSelectionChange = null;

       this.game.makeDiscardSelectable(false);
       document.getElementById('discard').onclick = null;

       this.game.makeVisibleSelectable(this.game.player_id, false);
       document.getElementById(`player_table_visible_wrapper_${this.game.player_id}`).onclick = null;
    }

    onUpdateActionButtons(args) {
    }

    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }
 