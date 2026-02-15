package tn.esprit.entities;

public class Tentative {

    private int id;
    private int score;
    private int duree;
    private String resultat;
    private int jeuId;
    private int userId;
    private int activiteId;

    public Tentative() {}

    public Tentative(int score, int duree, String resultat, int activiteId, int userId) {
        this.score = score;
        this.duree = duree;
        this.resultat = resultat;
        this.activiteId = activiteId;
        this.userId = userId;
    }

    public Tentative(int id, int score, int duree, String resultat, int jeuId, int userId) {
        this.id = id;
        this.score = score;
        this.duree = duree;
        this.resultat = resultat;
        this.jeuId = jeuId;
        this.userId = userId;
    }
    public int getActiviteId() {
        return activiteId;
    }

    public int getId() { return id; }
    public int getScore() { return score; }
    public int getDuree() { return duree; }
    public String getResultat() { return resultat; }
    public int getJeuId() { return jeuId; }
    public int getUserId() { return userId; }
}
