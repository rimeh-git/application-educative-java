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

    public Activite(int id, String imageUrl, String bonneReponse,
                    int score, int duree, String resultat,
                    int jeuId, String question, String typeActivite) {

        this.id = id;
        this.imageUrl = imageUrl;
        this.bonneReponse = bonneReponse;
        this.score = score;
        this.duree = duree;
        this.resultat = resultat;
        this.jeuId = jeuId;
        this.question = question;
        this.typeActivite = typeActivite;
    }



    public int getId() { return id; }
    public String getImageUrl() { return imageUrl; }
    public String getBonneReponse() { return bonneReponse; }
    public int getScore() { return score; }
    public int getDuree() { return duree; }
    public String getResultat() { return resultat; }
    public int getJeuId() { return jeuId; }
    public String getQuestion() { return question; }
    public String getTypeActivite() { return typeActivite; }
    public Activite(String imageUrl, String bonneReponse,
                    int score, int duree, String resultat,
                    int jeuId) {

        this.imageUrl = imageUrl;
        this.bonneReponse = bonneReponse;
        this.score = score;
        this.duree = duree;
        this.resultat = resultat;
        this.jeuId = jeuId;
    }



    public void setQuestion(String question) {
        this.question = question;
    }

    public void setTypeActivite(String typeActivite) {
        this.typeActivite = typeActivite;
    }

    public void setImageUrl(String imageUrl) {
        this.imageUrl = imageUrl;
    }

    public void setBonneReponse(String bonneReponse) {
        this.bonneReponse = bonneReponse;
    }

    public void setScore(int score) {
        this.score = score;
    }

    public void setDuree(int duree) {
        this.duree = duree;
    }

    public void setResultat(String resultat) {
        this.resultat = resultat;
    }

    public void setJeuId(int jeuId) {
        this.jeuId = jeuId;
    }

    @Override
    public String toString() {
        return "Activite{" +
                "id=" + id +
                ", question='" + question + '\'' +
                ", bonneReponse='" + bonneReponse + '\'' +
                ", score=" + score +
                ", jeuId=" + jeuId +
                '}';
    }
}
