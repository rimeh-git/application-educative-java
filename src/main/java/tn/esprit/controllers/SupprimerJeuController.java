package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.Alert;
import javafx.scene.control.ButtonType;
import javafx.scene.control.ListView;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceJeuEducatif;
import tn.esprit.utils.Navigation;

import java.net.URL;
import java.sql.SQLException;
import java.util.Optional;
import java.util.ResourceBundle;

public class SupprimerJeuController implements Initializable {

    @FXML
    private ListView<JeuEducatif> listeJeux;

    private final ServiceJeuEducatif service = new ServiceJeuEducatif();

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        try {
            listeJeux.getItems().setAll(service.afficher());
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void supprimer() {

        JeuEducatif selected = listeJeux.getSelectionModel().getSelectedItem();

        if (selected == null) {
            Alert alert = new Alert(Alert.AlertType.WARNING);
            alert.setTitle("Attention");
            alert.setHeaderText(null);
            alert.setContentText("Sélectionnez un jeu");
            alert.showAndWait();
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setHeaderText(null);
        confirm.setContentText("Voulez-vous supprimer ce jeu ?");

        Optional<ButtonType> result = confirm.showAndWait();

        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                service.supprimer(selected.getId());
                listeJeux.getItems().remove(selected);

                Alert alert = new Alert(Alert.AlertType.INFORMATION);
                alert.setTitle("Succès");
                alert.setHeaderText(null);
                alert.setContentText("Jeu supprimé");
                alert.showAndWait();

            } catch (Exception e) {
                Alert alert = new Alert(Alert.AlertType.ERROR);
                alert.setTitle("Erreur");
                alert.setHeaderText(null);
                alert.setContentText(e.getMessage());
                alert.showAndWait();
            }
        }
    }

    @FXML
    private void retour() {
        Navigation.go("/listeJeux.fxml", listeJeux);
    }
}
