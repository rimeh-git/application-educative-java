package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.entities.Activite;
import tn.esprit.services.ServiceActivite;
import java.util.List;



public class DetailsJeuController {
    private JeuEducatif jeu;

    @FXML private ImageView imageView;
    @FXML private Label typeLabel;
    @FXML private Label niveauLabel;
    @FXML private Label descriptionLabel;

    public void setJeu(JeuEducatif jeu) {
        this.jeu = jeu;

        typeLabel.setText(jeu.getType());
        descriptionLabel.setText(jeu.getDescription());
        niveauLabel.setText(jeu.getNiveau());

        if (jeu.getImage() != null && !jeu.getImage().isEmpty()) {
            Image img = new Image("file:" + jeu.getImage());
            imageView.setImage(img);
        }
    }


    @FXML
    private void retour() {
        Stage stage = (Stage) typeLabel.getScene().getWindow();
        stage.close();
    }
    @FXML
    private void jouerQuiz() {
        List<Activite> activites = null;
        try {
            ServiceActivite serviceActivite = new ServiceActivite();
            activites = serviceActivite.getByJeuId(jeu.getId());

            FXMLLoader loader =
                    new FXMLLoader(getClass().getResource("/JouerQuiz.fxml"));
            Parent root = loader.load();

            JouerQuizController controller = loader.getController();
            controller.setQuestions(activites);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Quiz");
            stage.show();


            Stage currentStage = (Stage) typeLabel.getScene().getWindow();
            currentStage.close();

        } catch (Exception e) {
            e.printStackTrace();
        }
        System.out.println("ID jeu: " + jeu.getId());
        System.out.println("Activités trouvées: " + activites.size());


    }

}


