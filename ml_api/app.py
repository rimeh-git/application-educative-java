from flask import Flask, request, jsonify
from sklearn.ensemble import RandomForestClassifier
import numpy as np

app = Flask(__name__)

# Features: [taux_annulation, progression_moyenne, score_agitation, nb_suivis, score_apres_moyen]
# Labels:   0 = stable, 1 = risque abandon
X = [
    [0.0,  3.0, 0, 5, 8.0],  # stable
    [0.0,  2.0, 0, 4, 7.0],  # stable
    [0.1,  1.5, 1, 3, 6.0],  # stable
    [0.1,  1.0, 0, 6, 7.5],  # stable
    [0.2,  0.5, 1, 2, 5.0],  # stable
    [0.0,  2.5, 0, 8, 8.5],  # stable
    [0.3,  0.0, 1, 2, 4.5],  # risque
    [0.5, -1.0, 2, 1, 3.0],  # risque
    [0.6, -2.0, 2, 1, 2.5],  # risque
    [0.8, -3.0, 3, 0, 2.0],  # risque
    [0.9, -1.5, 3, 0, 1.5],  # risque
    [0.7,  0.0, 2, 1, 3.5],  # risque
    [0.4, -0.5, 2, 2, 4.0],  # risque
    [0.0,  1.0, 0, 3, 6.0],  # stable
    [0.2,  2.0, 1, 4, 7.0],  # stable
    [0.6, -1.0, 3, 1, 2.0],  # risque
    [0.0,  3.0, 0, 7, 9.0],  # stable
    [0.5,  0.0, 2, 0, 3.0],  # risque
]
y = [0,0,0,0,0,0, 1,1,1,1,1,1,1, 0,0, 1, 0, 1]

model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X, y)

@app.route('/predict', methods=['POST'])
def predict():
    d = request.get_json()

    features = [[
        float(d.get('taux_annulation',     0)),
        float(d.get('progression_moyenne', 0)),
        float(d.get('score_agitation',     0)),
        float(d.get('nb_suivis',           0)),
        float(d.get('score_apres_moyen',   5)),
    ]]

    prediction  = int(model.predict(features)[0])
    probabilite = round(float(model.predict_proba(features)[0][1]) * 100, 1)

    return jsonify({
        'prediction':  prediction,
        'probabilite': probabilite,
        'statut':      'RISQUE_ABANDON' if prediction == 1 else 'NORMAL',
        'label':       '🔴 Risque d\'abandon' if prediction == 1 else '🟢 Patient stable',
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok'})

if __name__ == '__main__':
    app.run(port=5001)
