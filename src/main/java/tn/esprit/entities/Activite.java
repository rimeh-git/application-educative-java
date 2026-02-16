package tn.esprit.entities;

public class Activite {

    private int id;
    private String imageUrl;
    private String bonneReponse;
    private int score;
    private int duree;
    private String resultat;
    private int jeuId;
    private String question;
    private String typeActivite;

    public Activite() {}

    // 🔹 Constructeur principal complet
    public Activite(String imageUrl, String bonneReponse,
                    int score, int duree,
                    String resultat, int jeuId,
                    String question, String typeActivite) {

        this.imageUrl = imageUrl;
        this.bonneReponse = bonneReponse;
        this.score = score;
        this.duree = duree;
        this.resultat = resultat;
        this.jeuId = jeuId;
        this.question = question;
        this.typeActivite = typeActivite;
    }

    // 🔹 Constructeur avec ID
    public Activite(int id, String imageUrl, String bonneReponse,
                    int score, int duree,
                    String resultat, int jeuId,
                    String question, String typeActivite) {

        this(imageUrl, bonneReponse, score, duree, resultat, jeuId, question, typeActivite);
        this.id = id;
    }

    // 🔹 Constructeur simplifié (pour anciens appels)
    public Activite(String imageUrl, String bonneReponse,
                    int score, int duree,
                    String resultat, int jeuId) {

        this(imageUrl, bonneReponse, score, duree, resultat, jeuId, null, null);
    }

    // 🔹 Constructeur ultra simple (pour test Main)
    public Activite(String question, int score,
                    String typeActivite, int jeuId) {

        this(null, null, score, 0, null, jeuId, question, typeActivite);
    }

    // Getters
    public int getId() { return id; }
    public String getImageUrl() { return imageUrl; }
    public String getBonneReponse() { return bonneReponse; }
    public int getScore() { return score; }
    public int getDuree() { return duree; }
    public String getResultat() { return resultat; }
    public int getJeuId() { return jeuId; }
    public String getQuestion() { return question; }
    public String getTypeActivite() { return typeActivite; }

    // Setters
    public void setQuestion(String question) { this.question = question; }
    public void setScore(int score) { this.score = score; }
    public void setTypeActivite(String typeActivite) { this.typeActivite = typeActivite; }
    public void setJeuId(int jeuId) { this.jeuId = jeuId; }
}
