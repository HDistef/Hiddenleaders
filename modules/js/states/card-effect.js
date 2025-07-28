class CardEffectStates {
    game;
 
    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;

      if(!args.hasOwnProperty('card_action')) return;
      
      this.game.actionManager.addAction(args.card, args.card_action);

      if(args.hasOwnProperty('card_description')) this.game.actionManager.addDescription(args.card_description);

      this.game.actionManager.addArgument(args);
      
      this.game.actionManager.activateNextAction();
    }
 
    onLeavingState() {
       dojo.empty('customActions');
    }
 
    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
 
    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }
 