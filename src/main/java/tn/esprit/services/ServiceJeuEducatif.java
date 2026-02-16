package tn.esprit.services;

import tn.esprit.entities.Activite;
import tn.esprit.utils.MyDataBase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServiceActivite {

    private Connection cnx;

    public ServiceActivite() {
        cnx = MyDataBase.getInstance().getConnection();
    }

    // CREATE
    public void ajouter(Activite a) throws SQLException {
        String sql = "INSERT INTO activite (question, score, type_activite, jeu_id) VALUES (?,?,?,?)";

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, a.getQuestion());
            ps.setInt(2, a.getScore());
            ps.setString(3, a.getTypeActivite());
            ps.setInt(4, a.getJeuId());
            ps.executeUpdate();
        }
    }

    public List<Activite> afficher() throws SQLException {

        List<Activite> list = new ArrayList<>();
        String sql = "SELECT * FROM activite";

        PreparedStatement ps = cnx.prepareStatement(sql);
        ResultSet rs = ps.executeQuery();

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

    // UPDATE
    public void modifier(Activite a) throws SQLException {
        String sql = "UPDATE activite SET question=?, score=?, type_activite=?, jeu_id=? WHERE id=?";

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, a.getQuestion());
            ps.setInt(2, a.getScore());
            ps.setString(3, a.getTypeActivite());
            ps.setInt(4, a.getJeuId());
            ps.setInt(5, a.getId());
            ps.executeUpdate();
        }
    }

    // DELETE
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM activite WHERE id=?";

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }
    public List<Activite> getByJeuId(int jeuId) throws SQLException {
        List<Activite> list = new ArrayList<>();
        String sql = "SELECT * FROM activite WHERE jeu_id = ?";

        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setInt(1, jeuId);
        ResultSet rs = ps.executeQuery();

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
    // 🔹 TRI PAR SCORE
    public List<Activite> trierParScore() throws SQLException {

        List<Activite> list = afficher();
        list.sort((a1, a2) -> Integer.compare(a2.getScore(), a1.getScore()));
        return list;
    }

    // 🔹 RECHERCHE PAR RESULTAT
    public List<Activite> rechercherParResultat(String resultat) throws SQLException {

        List<Activite> list = afficher();
        List<Activite> result = new ArrayList<>();

        for (Activite a : list) {
            if (a.getResultat() != null &&
                    a.getResultat().equalsIgnoreCase(resultat)) {
                result.add(a);
            }
        }

        return result;
    }


}
