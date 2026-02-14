package tn.esprit.services;
import tn.esprit.entities.Tentative;


import tn.esprit.utils.MyDataBase;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;

public class ServiceTentative {
    private Connection cnx;

    public ServiceTentative() {
        cnx = MyDataBase.getInstance().getConnection();
    }

    public void ajouter(Tentative t) throws SQLException {
        String sql = "INSERT INTO tentative (score, duree, resultat, activite_id, user_id) VALUES (?,?,?,?,?)";
        PreparedStatement ps = cnx.prepareStatement(sql);
        ps.setInt(1, t.getScore());
        ps.setInt(2, t.getDuree());
        ps.setString(3, t.getResultat());
        ps.setInt(4, t.getActiviteId());
        ps.setInt(5, t.getUserId());
        ps.executeUpdate();
    }
}

