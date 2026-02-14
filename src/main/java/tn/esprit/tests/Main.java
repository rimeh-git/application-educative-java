package tn.esprit.tests;

import tn.esprit.entities.Activite;
import tn.esprit.entities.JeuEducatif;
import tn.esprit.services.ServiceActivite;
import tn.esprit.services.ServiceJeuEducatif;

public class Main {

    public static void main(String[] args) {

        ServiceJeuEducatif sj = new ServiceJeuEducatif();
        ServiceActivite sa = new ServiceActivite();

        try {

            sj.ajouter(new JeuEducatif(
                    "Image",
                    "Facile",
                    "Associer des images",
                    ""   // ou "" si pas d’image
            ));


            System.out.println("JEUX EDUCATIFS");
            sj.afficher().forEach(System.out::println);

            System.out.println("TRI PAR TYPE");
            sj.trierParType().forEach(System.out::println);


            sa.ajouter(new Activite(
                    "chat.png",
                    "Chat",
                    85,
                    120,
                    "Réussi",
                    1
            ));

            sa.ajouter(new Activite(
                    null,
                    null,
                    40,
                    200,
                    "Échoué",
                    1
            ));

            System.out.println("ACTIVITES");
            sa.afficher().forEach(System.out::println);

            System.out.println("TRI PAR SCORE");
            sa.trierParScore().forEach(System.out::println);

            System.out.println("RESULTAT = Réussi");
            sa.rechercherParResultat("Réussi")
                    .forEach(System.out::println);

        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }
}
