# Adding Your Images to Riget Zoo Adventures

## 📁 Where to Put Your Images

All images should go in: `public/assets/images/`

## 🖼️ Images Needed

The application expects these images (all are optional - placeholders will show if missing):

### Homepage (`src/Views/home.php`)
- **logo.png** - Your zoo logo (transparent PNG recommended)
- **deer.jpg** - Hero background image
- **hotel.jpg** - Accommodation card image
- **train.jpg** - Attractions card image
- **ticket.jpg** - Tickets card image

### Tickets Pages
- **giraffe.jpg** - Standard ticket page
- **seal.jpg** - Premium ticket page

### Attractions Page
- **train.jpg** - Zoo train/tram
- **giftshop.jpg** - Gift shop
- **res.jpg** - Restaurant

### Accommodations
Images are pulled from database `image_url` field
- Example: safari-lodge.jpg, jungle-bungalow.jpg, etc.

## 📋 Quick Setup

### Option 1: Use Your Own Images

1. Put your images in `public/assets/images/`
2. Name them according to the list above
3. Recommended sizes:
   - Logo: 200x200px (PNG with transparency)
   - Hero: 1920x1080px
   - Cards: 400x300px
   - Accommodations: 800x600px

### Option 2: Use Free Stock Photos

Download from these sites (all free):
- https://unsplash.com - Search for "zoo", "animals", "hotel"
- https://pexels.com - Search for "wildlife", "lodging"
- https://pixabay.com - Search for "safari", "giraffe"

### Option 3: Keep Using Placeholders

The app already has fallback placeholders! If an image is missing, it automatically shows a nice placeholder.

## 🎨 Example: Adding Your Logo

```bash
# 1. Save your logo as logo.png
# 2. Copy it to:
public/assets/images/logo.png

# Done! It will appear in the header automatically
```

## 🔄 How Placeholders Work

Each image has a fallback:
```html
<img src="/assets/images/deer.jpg" 
     onerror="this.src='https://via.placeholder.com/1920x1080/444/fff?text=Riget+Zoo+Adventures'">
```

If `deer.jpg` is missing, it shows a nice placeholder instead!

## 📸 Current Structure

```
public/
└── assets/
    ├── css/
    │   └── styles.css     ✅ Created
    ├── js/
    │   └── app.js         ✅ Created
    └── images/            📁 Add your images here!
        ├── logo.png       ❌ Add this
        ├── deer.jpg       ❌ Add this
        ├── hotel.jpg      ❌ Add this
        ├── train.jpg      ❌ Add this
        ├── ticket.jpg     ❌ Add this
        ├── giraffe.jpg    ❌ Add this
        ├── seal.jpg       ❌ Add this
        ├── giftshop.jpg   ❌ Add this
        └── res.jpg        ❌ Add this
```

## 🚀 Tips

1. **Optimize images** before uploading (use TinyPNG.com)
2. **Use consistent sizes** for better loading
3. **Name files descriptively** (no spaces, lowercase)
4. **Use JPG** for photos, **PNG** for logos with transparency

## 💡 No Images? No Problem!

The app works perfectly with the placeholder images! You can:
1. Launch the app first to see how it looks
2. Add images later one by one
3. The placeholders look professional and modern

---

**Need help?** The images are optional - the app will work great with placeholders!
