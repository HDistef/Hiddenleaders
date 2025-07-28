class ArtifactSetupStates {
    game;
    artifact_cards;
    player_table;
    
    constructor(game) { this.game = game; }
  
    onEnteringState(args) {
        if (!this.game.isCurrentPlayerActive()) return;

        this.player_table = this.game.getCurrentPlayerTable();
        
        const selectConfirm = () => {
            if(this.player_table.playerArtifact.getSelection().length == 0) return;
            
            this.game.takeActionNoLock("selectArtifact", {
                artifact_id: this.player_table.playerArtifact.getSelection()[0].id,
                player_id: this.game.player_id,
            });

            dojo.empty('customActions');
            this.player_table.playerArtifact.setSelectionMode("none");
            this.player_table.playerArtifact.onSelectionChange = null;
        };

        
        this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), selectConfirm);
        this.game.toggleButton("select_button", false);

        const handleChange = () => this.game.toggleButton("select_button", this.player_table.playerArtifact.getSelection().length == 1);
       
        this.player_table.playerArtifact.setSelectionMode("single");
        this.player_table.playerArtifact.onSelectionChange = handleChange;
     }
  
    onLeavingState() {
       dojo.empty('customActions');
       
       this.player_table.playerArtifact.setSelectionMode("none");
       this.player_table.playerArtifact.onSelectionChange = null;
    }
  
    onUpdateActionButtons(args) {
    }
  
    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }
  