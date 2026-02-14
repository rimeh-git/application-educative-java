package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import tn.esprit.entities.Activite;
import tn.esprit.services.ServiceActivite;

public class ActiviteController {

    @FXML
    private TextField reponseField;

    @FXML
    private Label resultatLabel;

    private int jeuId;

    public void setJeuId(int jeuId) {
        this.jeuId = jeuId;
    }

    @FXML
    private void validerActivite() {
        try {
            String reponse = reponseField.getText();
            String resultat = reponse.equalsIgnoreCase("chat") ? "Réussi" : "Échoué";

            Activite a = new Activite(
                    null,
                    "chat",
                    resultat.equals("Réussi") ? 100 : 0,
                    120,
                    resultat,
                    jeuId
            );

            new ServiceActivite().ajouter(a);
            resultatLabel.setText(resultat);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
