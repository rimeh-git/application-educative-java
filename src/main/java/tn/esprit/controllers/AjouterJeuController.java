package tn.esprit.controllers;

import javafx.animation.TranslateTransition;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.stage.FileChooser;
import javafx.stage.Stage;
import javafx.util.Duration;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceJeuEducatif;

import java.io.File;
import java.net.URL;
import java.util.ResourceBundle;

public class AjouterJeuController implements Initializable {

    @FXML private TextField typeField;
    @FXML private ComboBox<String> niveauCombo;

    @FXML private TextField imageField;

    @FXML
    private TextArea descField;

    private final ServiceJeuEducatif service = new ServiceJeuEducatif();

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        niveauCombo.getItems().addAll("Facile", "Moyen", "Difficile");
    }


    @FXML
    private void ajouter() {

        resetStyles();

        String type = typeField.getText().trim();
        String niveau = niveauCombo.getValue();
        String description = descField.getText().trim();
        String image = imageField.getText().trim();

        boolean valid = true;


        if (type.isEmpty()) {
            markInvalid(typeField);
            valid = false;
        }


        if (!type.matches("[a-zA-Z ]+")) {
            markInvalid(typeField);
            showError("Le type doit contenir uniquement des lettres.");
            valid = false;
        }


        if (niveau == null) {
            markInvalid(niveauCombo);
            valid = false;
        }


        if (description.isEmpty() || description.length() < 5) {
            markInvalid(descField);
            showError("La description doit contenir au moins 5 caractères.");
            valid = false;
        }


        if (image.isEmpty()) {
            markInvalid(imageField);
            showError("Veuillez choisir une image.");
            valid = false;
        }

        if (!valid) return;

        try {
            service.ajouter(new JeuEducatif(type, niveau, description, image));
            showSuccess("Jeu ajouté avec succès !");


            Stage stage = (Stage) typeField.getScene().getWindow();
            stage.close();


        } catch (Exception e) {
            showError(e.getMessage());
        }
    }

    @FXML
    private void choisirImage() {
        FileChooser fileChooser = new FileChooser();
        File file = fileChooser.showOpenDialog(null);

        if (file != null) {
            imageField.setText(file.getAbsolutePath());
        }
    }

    @FXML
    private void retour() {
        Stage stage = (Stage) typeField.getScene().getWindow();
        stage.close();

    }

    private void markInvalid(Control field) {
        field.setStyle("-fx-border-color: red; -fx-border-width: 2;");
        shake(field);
    }

    private void resetStyles() {
        typeField.setStyle(null);
        niveauCombo.setStyle(null);
        descField.setStyle(null);
        imageField.setStyle(null);
    }

    private void shake(Control field) {
        TranslateTransition tt = new TranslateTransition(Duration.millis(70), field);
        tt.setFromX(0);
        tt.setByX(10);
        tt.setCycleCount(6);
        tt.setAutoReverse(true);
        tt.play();
    }

    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(null);
        alert.setContentText(msg);
        alert.showAndWait();
    }

    private void showSuccess(String msg) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(null);
        alert.setContentText(msg);
        alert.showAndWait();
    }
}
