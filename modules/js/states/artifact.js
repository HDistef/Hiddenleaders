class ArtifactStates {
    game;
    constructor(game) { this.game = game; }
  
    onEnteringState(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        
        var player_table = this.game.getCurrentPlayerTable();

        this.game.addPrimaryActionButton(`artifact_button`, _(`Play ${player_table.playerArtifact.getCards()[0].name}`), () => {
            this.game.takeAction("playArtifact", {});
         });
         this.game.addDangerActionButton(`pass_button`, _("Pass"), () => {
            this.game.takeAction("noAction", {
                isArtifact: true
            });
         });
     }
  
    onLeavingState() {
       dojo.empty('customActions');
    }
  
    onUpdateActionButtons(args) {
    }
  
    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }
  