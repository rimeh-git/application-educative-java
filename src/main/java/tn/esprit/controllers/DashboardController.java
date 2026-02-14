package tn.esprit.controllers;

import javafx.animation.TranslateTransition;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.layout.StackPane;
import javafx.util.Duration;

public class DashboardController {

    @FXML
    private StackPane contentArea;

    @FXML
    public void initialize() {
        loadPage("listeJeux.fxml");
    }

    @FXML
    private void openJeux() {
        loadPage("listeJeux.fxml");
    }

    private void loadPage(String page) {
        try {
            FXMLLoader loader =
                    new FXMLLoader(getClass().getResource("/" + page));
            Node pane = loader.load();

            pane.setTranslateX(1000);

            contentArea.getChildren().setAll(pane);

            TranslateTransition tt =
                    new TranslateTransition(Duration.millis(400), pane);
            tt.setToX(0);
            tt.play();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
