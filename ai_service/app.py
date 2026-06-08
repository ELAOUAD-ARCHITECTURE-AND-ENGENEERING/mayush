import os
from flask import Flask, request, jsonify
from PIL import Image
from transformers import BlipProcessor, BlipForConditionalGeneration
import torch

app = Flask(__name__)

# Initialize model globally so it's only loaded once when the server starts
print("Loading AI Model. This may take a minute on first run...")
processor = BlipProcessor.from_pretrained("Salesforce/blip-image-captioning-base")
model = BlipForConditionalGeneration.from_pretrained("Salesforce/blip-image-captioning-base")
print("AI Model loaded successfully! Listening for image queries...")

@app.route('/predict', methods=['POST'])
def predict():
    if 'image' not in request.files:
        return jsonify({'error': 'No image provided'}), 400
        
    file = request.files['image']
    if file.filename == '':
        return jsonify({'error': 'No selected file'}), 400
        
    try:
        raw_image = Image.open(file.stream).convert('RGB')
        
        # Generate a general caption
        text = "a photograph of a"
        inputs = processor(raw_image, text, return_tensors="pt")
        
        out = model.generate(**inputs, max_length=50)
        caption = processor.decode(out[0], skip_special_tokens=True)
        
        # We can extract the main nouns and adjectives or just return the caption as search keywords
        keywords = caption.replace('a photograph of a ', '').replace('a photograph of ', '')
        
        # Extract just the main object by splitting off the rest of the sentence
        split_phrases = [' sitting ', ' standing ', ' laying ', ' lying ', ' in ', ' on ', ' at ', ' with ', ' next to ', ' beside ', ' and ', ' inside ', ' outside ', ' near ', ' hanging ']
        for phrase in split_phrases:
            if phrase in keywords:
                keywords = keywords.split(phrase)[0]
                
        keywords = keywords.strip()
        
        return jsonify({
            'success': True,
            'caption': caption,
            'keywords': keywords
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    # Run slightly on a non-standard port so it doesn't conflict with Laravel
    app.run(host='127.0.0.1', port=5001)
