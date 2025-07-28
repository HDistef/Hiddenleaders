class SelectOpponentStates {
  game;
  client_args;

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
    this.client_args = args;

    
    this.client_args.selectable_opponents.forEach(target_id => {
      var description = this.game.getPlayerName(target_id);
      if(this.client_args.hasOwnProperty('action')) description += ' : ' + this[this.client_args.action](target_id);

      this.game.addPrimaryActionButton(`select_opponent`, description, () => this.selectOpponentConfirm(target_id));
    });
  }
  
  selectOpponentConfirm(selected_opponent) {
    this.game.actionManager.addClientArgument(selected_opponent);
    this.game.actionManager.activateNextAction();
  }

  select_opponent_AlmostEvilScholar(target_id) {
    return this.game.empireCounters[target_id].getValue();
  }

  select_opponent_AlmostEvilScholar(target_id) {
    return this.game.tribesCounters[target_id].getValue();
  }

  restoreGameState() {
      return new Promise<boolean>((resolve) => resolve(true));
  }
}