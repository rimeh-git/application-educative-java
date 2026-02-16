package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.Activite;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceActivite;
import tn.esprit.services.ServiceJeuEducatif;

import java.sql.SQLException;
import java.util.List;

public class AjouterActiviteController {

    @FXML private TextField questionField;
    @FXML private TextField scoreField;

    @FXML private ComboBox<JeuEducatif> jeuComboBox;

    private final ServiceActivite service = new ServiceActivite();
    private final ServiceJeuEducatif serviceJeu = new ServiceJeuEducatif();

    @FXML
    public void initialize() {

        try {
            List<JeuEducatif> jeux = serviceJeu.afficher();
            jeuComboBox.getItems().addAll(jeux);

            // Afficher le type du jeu dans la ComboBox
            jeuComboBox.setCellFactory(param -> new ListCell<>() {
                @Override
                protected void updateItem(JeuEducatif item, boolean empty) {
                    super.updateItem(item, empty);
                    setText(empty || item == null ? null : item.getType());
                }
            });

            jeuComboBox.setButtonCell(new ListCell<>() {
                @Override
                protected void updateItem(JeuEducatif item, boolean empty) {
                    super.updateItem(item, empty);
                    setText(empty || item == null ? "Choisir un jeu" : item.getType());
                }
            });

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void ajouterActivite() {

        String question = questionField.getText().trim();
        String scoreText = scoreField.getText().trim();
        String type = "quiz";  // valeur automatique
        JeuEducatif jeu = jeuComboBox.getValue();

        if (question.isEmpty() || scoreText.isEmpty() || type.isEmpty() || jeu == null) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Tous les champs sont obligatoires !");
            return;
        }

        int score;

        try {
            score = Integer.parseInt(scoreText);
        } catch (NumberFormatException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Score doit être un nombre !");
            return;
        }

        if (score <= 0) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Score doit être positif !");
            return;
        }

        try {

            Activite a = new Activite(
                    question,
                    score,
                    "Quiz",
                    jeu.getId()  
            );

            service.ajouter(a);

            showAlert(Alert.AlertType.INFORMATION, "Succès", "Activité ajoutée !");
            fermer();

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur Base", e.getMessage());
        }
    }

    private void showAlert(Alert.AlertType type, String titre, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void fermer() {
        Stage stage = (Stage) questionField.getScene().getWindow();
        stage.close();
    }
}
