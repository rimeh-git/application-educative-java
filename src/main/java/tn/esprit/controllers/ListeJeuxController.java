package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Cursor;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceJeuEducatif;

import java.sql.SQLException;
import java.util.List;
import java.util.stream.Collectors;

public class ListeJeuxController {

    @FXML private TextField searchField;
    @FXML private Label compteurLabel;
    @FXML private Pagination pagination;

    private static final int ITEMS_PER_PAGE = 6;

    private final ServiceJeuEducatif service = new ServiceJeuEducatif();
    private List<JeuEducatif> jeux;

    @FXML
    public void initialize() {

        try {
            jeux = service.afficher();
            afficherJeux(jeux);

            searchField.textProperty().addListener((obs, oldVal, newVal) -> {
                List<JeuEducatif> filtered =
                        jeux.stream()
                                .filter(j -> j.getType().toLowerCase()
                                        .contains(newVal.toLowerCase()))
                                .collect(Collectors.toList());

                afficherJeux(filtered);
            });

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void afficherJeux(List<JeuEducatif> liste) {

        compteurLabel.setText("📦 " + liste.size() + " jeux trouvés");

        int pageCount = (int) Math.ceil((double) liste.size() / ITEMS_PER_PAGE);
        pagination.setPageCount(pageCount == 0 ? 1 : pageCount);

        pagination.setPageFactory(pageIndex -> {

            GridPane page = new GridPane();
            page.setHgap(30);
            page.setVgap(30);
            page.setPadding(new Insets(40));
            page.setAlignment(Pos.TOP_CENTER);

            int from = pageIndex * ITEMS_PER_PAGE;
            int to = Math.min(from + ITEMS_PER_PAGE, liste.size());

            int col = 0;
            int row = 0;

            for (int i = from; i < to; i++) {

                VBox card = createCard(liste.get(i));
                page.add(card, col, row);

                col++;
                if (col == 3) {
                    col = 0;
                    row++;
                }
            }

            return page;
        });
    }

    private VBox createCard(JeuEducatif jeu) {

        VBox card = new VBox(10);
        card.getStyleClass().add("game-card");
        card.setPrefWidth(280);
        card.setCursor(Cursor.HAND);


        ImageView imageView = new ImageView();
        imageView.setFitWidth(260);
        imageView.setFitHeight(150);
        imageView.setPreserveRatio(false);

        if (jeu.getImage() != null && !jeu.getImage().isEmpty()) {
            try {
                Image img = new Image("file:" + jeu.getImage());
                imageView.setImage(img);
            } catch (Exception ignored) {}
        }


        Label title = new Label("🎮 " +
                (jeu.getType() != null ? jeu.getType() : "Sans nom"));
        title.getStyleClass().add("card-title");


        Label desc = new Label(
                (jeu.getDescription() != null ?
                        jeu.getDescription() : "Pas de description"));
        desc.setWrapText(true);
        desc.getStyleClass().add("card-desc");


        Label badge = new Label(
                jeu.getNiveau() != null ? jeu.getNiveau() : "Non défini");
        badge.getStyleClass().add("badge");

        if (jeu.getNiveau() != null) {
            switch (jeu.getNiveau()) {
                case "Facile" -> badge.getStyleClass().add("badge-green");
                case "Moyen" -> badge.getStyleClass().add("badge-orange");
                case "Difficile" -> badge.getStyleClass().add("badge-red");
            }
        }


        Button btnDetails = new Button("📄 Détails");
        Button btnModifier = new Button("✏ Modifier");
        Button btnSupprimer = new Button("🗑 Supprimer");

        btnDetails.getStyleClass().add("btn-primary");
        btnModifier.getStyleClass().add("btn-edit");
        btnSupprimer.getStyleClass().add("btn-delete");


        btnDetails.setOnMouseClicked(e -> e.consume());
        btnModifier.setOnMouseClicked(e -> e.consume());
        btnSupprimer.setOnMouseClicked(e -> e.consume());

        btnDetails.setOnAction(e -> {
            System.out.println("DETAILS CLICKED");
            ouvrirDetails(jeu);
        });

        btnModifier.setOnAction(e -> {
            try {
                FXMLLoader loader =
                        new FXMLLoader(getClass().getResource("/modifierJeu.fxml"));
                Parent root = loader.load();

                ModifierJeuController controller = loader.getController();
                controller.setJeu(jeu);

                Stage stage = new Stage();
                stage.setScene(new Scene(root));
                stage.setTitle("Modifier un jeu");
                stage.showAndWait();

                jeux = service.afficher();
                afficherJeux(jeux);

            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });

        btnSupprimer.setOnAction(e -> {
            try {
                service.supprimer(jeu.getId());
                jeux = service.afficher();
                afficherJeux(jeux);
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });

        HBox actions = new HBox(10, btnDetails, btnModifier, btnSupprimer);
        actions.setAlignment(Pos.CENTER);

        card.getChildren().addAll(imageView, title, badge, desc, actions);




        return card;
    }

    private void ouvrirDetails(JeuEducatif jeu) {
        try {
            FXMLLoader loader =
                    new FXMLLoader(getClass().getResource("/DetailsJeu.fxml"));
            Parent root = loader.load();

            DetailsJeuController controller = loader.getController();
            controller.setJeu(jeu);

            Stage stage = new Stage();
            stage.setTitle("Détails du jeu");
            stage.setScene(new Scene(root, 800, 600));
            stage.show();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void goToAjouter() {
        try {
            FXMLLoader loader =
                    new FXMLLoader(getClass().getResource("/ajouterJeu.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Ajouter un jeu");
            stage.setScene(new Scene(root));
            stage.initOwner(searchField.getScene().getWindow());
            stage.showAndWait();

            jeux = service.afficher();
            afficherJeux(jeux);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
