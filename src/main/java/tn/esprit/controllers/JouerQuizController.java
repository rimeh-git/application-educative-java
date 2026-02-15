package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.TextField;
import tn.esprit.entities.Activite;
import tn.esprit.entities.Tentative;
import tn.esprit.services.ServiceTentative;
import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.util.Duration;
import java.util.List;




public class JouerQuizController {
    @FXML private Label timerLabel;
    @FXML private ProgressBar progressBar;

    private Timeline timeline;
    private int tempsRestant = 10;
    @FXML private Label questionLabel;
    @FXML private Label numeroQuestionLabel;
    @FXML private Label scoreLabel;
    @FXML private TextField reponseField;

    private List<Activite> questions;
    private int index = 0;
    private int scoreTotal = 0;

    public void setQuestions(List<Activite> questions) {
        this.questions = questions;
        afficherQuestion();
    }

    private void afficherQuestion() {

        if (questions == null || questions.isEmpty()) {
            questionLabel.setText("Aucune question disponible");
            return;
        }

        if (index >= questions.size()) {
            questionLabel.setText("Quiz terminé !");
            numeroQuestionLabel.setText("");
            scoreLabel.setText("Score final : " + scoreTotal);
            reponseField.setDisable(true);
            return;
        }

        Activite a = questions.get(index);

        numeroQuestionLabel.setText("Question " + (index + 1));
        questionLabel.setText(a.getQuestion());
        reponseField.clear();

        progressBar.setProgress((double) index / questions.size());
    }

    @FXML
    private void verifierReponse() {

        if (questions == null || questions.isEmpty()) return;
        if (index >= questions.size()) return;

        Activite a = questions.get(index);

        if (reponseField.getText().equalsIgnoreCase(a.getBonneReponse())) {
            scoreTotal += a.getScore();
            questionLabel.setText("✅ Bonne réponse !");
        } else {
            questionLabel.setText("❌ Mauvaise réponse !");
        }

        index++;

        Timeline pause = new Timeline(
                new KeyFrame(Duration.seconds(1), e -> afficherQuestion())
        );
        pause.play();
    }



    private void enregistrerTentative() {

        try {

            ServiceTentative service = new ServiceTentative();

            Tentative t = new Tentative(
                    scoreTotal,
                    0,
                    "Terminé",
                    questions.get(0).getId(),
                    1
            );

            service.ajouter(t);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    @FXML
    private void rejouerQuiz() {
        index = 0;
        scoreTotal = 0;
        reponseField.setDisable(false);
        afficherQuestion();
    }

}
