package tn.esprit.services;

import tn.esprit.entities.Activite;
import tn.esprit.utils.MyDataBase;

import java.sql.*;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;
import java.util.stream.Collectors;

public class ServiceActivite {

    private Connection cnx;

    public ServiceActivite() {
        cnx = MyDataBase.getInstance().getConnection();
    }

    // ✅ AJOUTER
    public void ajouter(Activite a) throws SQLException {

        String sql = "INSERT INTO activite (image_url, bonne_reponse, score, duree, resultat, jeu_id, question, type_activite) VALUES (?,?,?,?,?,?,?,?)";

        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setString(1, a.getImageUrl());
        ps.setString(2, a.getBonneReponse());
        ps.setInt(3, a.getScore());
        ps.setInt(4, a.getDuree());
        ps.setString(5, a.getResultat());
        ps.setInt(6, a.getJeuId());
        ps.setString(7, a.getQuestion());
        ps.setString(8, a.getTypeActivite());

        ps.executeUpdate();
    }

    // ✅ AFFICHER
    public List<Activite> afficher() throws SQLException {

        String sql = "SELECT * FROM activite";
        Statement st = cnx.createStatement();
        ResultSet rs = st.executeQuery(sql);

        List<Activite> list = new ArrayList<>();

        while (rs.next()) {

            list.add(new Activite(
                    rs.getInt("id"),
                    rs.getString("image_url"),
                    rs.getString("bonne_reponse"),
                    rs.getInt("score"),
                    rs.getInt("duree"),
                    rs.getString("resultat"),
                    rs.getInt("jeu_id"),
                    rs.getString("question"),
                    rs.getString("type_activite")
            ));
        }

        return list;
    }

    // ✅ TRI PAR SCORE
    public List<Activite> trierParScore() throws SQLException {

        return afficher().stream()
                .sorted(Comparator.comparingInt(Activite::getScore).reversed())
                .collect(Collectors.toList());
    }

    // ✅ RECHERCHE PAR RESULTAT
    public List<Activite> rechercherParResultat(String resultat) throws SQLException {

        return afficher().stream()
                .filter(a -> a.getResultat().equalsIgnoreCase(resultat))
                .collect(Collectors.toList());
    }

    // ✅ RECUPERER PAR JEU ID
    public List<Activite> getByJeuId(int jeuId) throws SQLException {

        String sql = "SELECT * FROM activite WHERE jeu_id = ?";
        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setInt(1, jeuId);

        ResultSet rs = ps.executeQuery();
        List<Activite> list = new ArrayList<>();

        while (rs.next()) {

            list.add(new Activite(
                    rs.getInt("id"),
                    rs.getString("image_url"),
                    rs.getString("bonne_reponse"),
                    rs.getInt("score"),
                    rs.getInt("duree"),
                    rs.getString("resultat"),
                    rs.getInt("jeu_id"),
                    rs.getString("question"),
                    rs.getString("type_activite")
            ));
        }

        return list;
    }
}