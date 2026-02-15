package tn.esprit.services;

import tn.esprit.entities.JeuEducatif;
import tn.esprit.utils.MyDataBase;

import java.sql.*;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;

public class ServiceJeuEducatif implements IService<JeuEducatif> {

    private Connection cnx;

    public ServiceJeuEducatif() {
        cnx = MyDataBase.getInstance().getConnection();
    }

    @Override
    public void ajouter(JeuEducatif j) throws SQLException {
        String sql = "INSERT INTO jeu_educatif (type, niveau, description, image) VALUES (?,?,?,?)";
        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setString(1, j.getType());
        ps.setString(2, j.getNiveau());
        ps.setString(3, j.getDescription());
        ps.setString(4, j.getImage());
        ps.executeUpdate();
    }

    @Override
    public List<JeuEducatif> afficher() throws SQLException {
        List<JeuEducatif> list = new ArrayList<>();
        ResultSet rs = cnx.createStatement().executeQuery("SELECT * FROM jeu_educatif");

        while (rs.next()) {
            list.add(new JeuEducatif(
                    rs.getInt("id"),
                    rs.getString("type"),
                    rs.getString("niveau"),
                    rs.getString("description"),
                    rs.getString("image")
            ));
        }
        return list;
    }

    @Override
    public void modifier(JeuEducatif j) throws SQLException {
        String sql = "UPDATE jeu_educatif SET type=?, niveau=?, description=?, image=? WHERE id=?";
        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setString(1, j.getType());
        ps.setString(2, j.getNiveau());
        ps.setString(3, j.getDescription());
        ps.setString(4, j.getImage());
        ps.setInt(5, j.getId());
        ps.executeUpdate();
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM jeu_educatif WHERE id=?";
        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();
    }

    // Méthodes supplémentaires (non obligatoires dans l'interface)

    public List<JeuEducatif> trierParType() throws SQLException {
        return afficher().stream()
                .sorted(Comparator.comparing(JeuEducatif::getType))
                .toList();
    }

    public List<JeuEducatif> rechercherStream(String motCle) throws SQLException {
        return afficher().stream()
                .filter(j ->
                        (j.getType() != null &&
                                j.getType().toLowerCase().contains(motCle.toLowerCase()))
                                ||
                                (j.getDescription() != null &&
                                        j.getDescription().toLowerCase().contains(motCle.toLowerCase()))
                )
                .toList();
    }
}
