package tn.esprit.controllers;

import javafx.animation.TranslateTransition;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.FileChooser;
import javafx.stage.Stage;
import javafx.util.Duration;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceJeuEducatif;

import java.io.File;
import java.net.URL;
import java.util.ResourceBundle;

public class ModifierJeuController implements Initializable {

    @FXML private TextField typeField;
    @FXML private ComboBox<String> niveauCombo;
    private File selectedImageFile;

    @FXML
    private TextArea descField;
    @FXML private TextField imageField;

    private JeuEducatif jeu;

    private final ServiceJeuEducatif service = new ServiceJeuEducatif();

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        niveauCombo.getItems().addAll("Facile", "Moyen", "Difficile");
    }

    // ✅ UNE SEULE méthode setJeu
    public void setJeu(JeuEducatif jeu) {
        this.jeu = jeu;

        typeField.setText(jeu.getType());
        niveauCombo.setValue(jeu.getNiveau());
        descField.setText(jeu.getDescription());
        imageField.setText(jeu.getImage());



    }

    @FXML
    private void choisirImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");

        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );

        File file = fileChooser.showOpenDialog(typeField.getScene().getWindow());

        if (file != null) {
            selectedImageFile = file;
            imageField.setText(file.getAbsolutePath());

        }
    }

    @FXML
    private void modifier() {

        resetStyles();

        String type = typeField.getText().trim();
        String niveau = niveauCombo.getValue();
        String description = descField.getText().trim();
        String imagePath = imageField.getText();

        boolean valid = true;

        if (type.isEmpty()) {
            markInvalid(typeField);
            valid = false;
        }

        if (niveau == null) {
            markInvalid(niveauCombo);
            valid = false;
        }

        if (description.isEmpty()) {
            markInvalid(descField);
            valid = false;
        }

        if (!type.matches("[a-zA-Z ]+")) {
            markInvalid(typeField);
            showAlert("Erreur", "Le type doit contenir uniquement des lettres");
            valid = false;
        }

        if (description.length() < 5) {
            markInvalid(descField);
            showAlert("Erreur", "La description doit contenir au moins 5 caractères");
            valid = false;
        }

        if (!valid) return;

        try {
            jeu.setType(type);
            jeu.setNiveau(niveau);
            jeu.setDescription(description);
            jeu.setImage(imagePath);

            service.modifier(jeu);

            showAlert("Succès", "Jeu modifié avec succès");

            Stage stage = (Stage) typeField.getScene().getWindow();
            stage.close();

        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
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
    }

    private void shake(Control field) {
        TranslateTransition tt = new TranslateTransition(Duration.millis(70), field);
        tt.setFromX(0);
        tt.setByX(10);
        tt.setCycleCount(6);
        tt.setAutoReverse(true);
        tt.play();
    }

    private void showAlert(String title, String msg) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(msg);
        alert.showAndWait();
    }

}
