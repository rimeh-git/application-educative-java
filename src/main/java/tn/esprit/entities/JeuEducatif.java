package tn.esprit.entities;

public class JeuEducatif {

    private int id;
    private String type;
    private String niveau;
    private String description;
        private String image ;

    public JeuEducatif() {}

    public JeuEducatif(String type, String niveau, String description,String image) {
        this.type = type;
        this.niveau = niveau;
        this.description = description;
        this.image = image;

    }

    public JeuEducatif(int id, String type, String niveau, String description,String image) {
        this.id = id;
        this.type = type;
        this.niveau = niveau;
        this.description = description;
        this.image=image;
    }

    public int getId() { return id; }
    public String getType() { return type; }
    public String getNiveau() { return niveau; }
    public String getDescription() { return description; }

    public void setId(int id) { this.id = id; }
    public void setType(String type) { this.type = type; }
    public void setNiveau(String niveau) { this.niveau = niveau; }
    public void setDescription(String description) { this.description = description; }

    public String getImage() { return image; }
    public void setImage(String image) { this.image = image; }

    @Override
    public String toString() {
        return "JeuEducatif{" +
                "id=" + id +
                ", type='" + type + '\'' +
                ", niveau='" + niveau + '\'' +
                ", description='" + description + '\'' +
                ", image='" + image + '\'' +
                '}';
    }
}
