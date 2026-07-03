<div align="center">
  <img src="public/assets/img/logo.svg" alt="Mayush Marketplace Logo" width="300" />
  
  <h1>Mayush Marketplace</h1>
  <p><strong>The 1st Premium Interior Design & Furniture Marketplace Platform in Morocco.</strong></p>

  [![Laravel](https://img.shields.io/badge/Laravel-10.x/11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![Design](https://img.shields.io/badge/UI/UX-Premium_Glassmorphism-0F766E?style=for-the-badge&logo=figma&logoColor=white)](#design-system)
</div>

<hr/>

## 🛋️ Vision & Goal

**Mayush** is not just an e-commerce platform; it is a curated ecosystem designed to connect discerning buyers with premium furniture, artisanal home decor, lighting solutions, and interior design materials. 

Our goal is to elevate the online shopping experience for interior design in Morocco by providing a **multi-vendor marketplace** that blends high-end aesthetics, powerful B2B/B2C commerce tools, and a platform for local artisans to tell their stories.

---

## ✨ Key Features

### For Buyers
*   **Curated Collections:** Browse through highly categorized collections (Office Furniture, Lighting, Wallcoverings, Textiles, etc.).
*   **Premium Checkout Experience:** A streamlined, GSAP-animated, 5-step elegant checkout process with AI-driven design insights.
*   **White Glove Delivery:** Built-in logic for premium installation services on qualifying orders.
*   **Artisan Discovery:** Dedicated artisan profiles featuring workshop videos, brand philosophies, and "Meet the Maker" galleries.

### For Sellers & Artisans
*   **Cyan Data-Dense Dashboard:** A beautifully designed, highly functional vendor panel (B-Dash) to manage inventory, track flash sales, and view analytics.
*   **Storytelling Capabilities:** Artisans can upload hero banners, workshop videos, and write their brand philosophy to connect with buyers emotionally.
*   **Flexible Product Management:** Support for digital products, physical goods, variants, and wholesale pricing.

### Core Platform Features
*   **Liquid Glass UI/UX:** A bespoke frontend "Metro" theme featuring glassmorphism, smooth micro-animations, and luxurious typography (`Playfair Display` & `Inter`).
*   **Advanced Mega Menu:** A responsive, interactive category navigation system.
*   **Flash Deals & Promotions:** Built-in scarcity marketing tools like countdown timers and cyber-style flash deal banners.
*   **Multi-language & Multi-currency:** Built to scale globally while dominating locally.

---

## 🛠️ Technology Stack

*   **Backend:** Laravel (PHP 8.x)
*   **Database:** MySQL / MariaDB
*   **Frontend Styling:** Custom CSS Architecture (`mayush-design-tokens.css`), Bootstrap 4/5 base.
*   **Frontend Interactivity:** jQuery, GSAP (GreenSock) for high-end animations, Slick Slider.
*   **Icons & Assets:** Line Awesome, Custom SVG iconography.

---

## 🎨 Design System & Charte Graphique

Mayush strictly adheres to a premium design language to ensure the platform feels luxurious and trustworthy.

*   **Primary Colors:** Midnight Navy (`#12192A`), Warm Gold (`#D6A24E`), Soft Orange (`#C98446`).
*   **Typography:** 
    *   *Headings:* `Playfair Display` (Elegant, Serif)
    *   *Body/UI:* `Inter` (Modern, Sans-Serif)
*   **Aesthetics:** "Liquid Glass" (frosted glass effects, soft shadows, warm borders).

*For a full breakdown of the design rules, refer to the [Charte Graphique Guide](docs/mayush_design_charte_graphique_guide.md).*

---

## 🚀 Installation & Local Setup

### Prerequisites
*   PHP >= 8.1
*   Composer
*   MySQL/MariaDB
*   Node.js & npm

### Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ELAOUAD-ARCHITECTURE-AND-ENGENEERING/mayush.git
   cd mayush
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update your `.env` file with your local database credentials.*

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Link Storage:**
   ```bash
   php artisan storage:link
   ```

6. **Serve the Application:**
   ```bash
   php artisan serve
   ```
   *The platform will be available at `http://localhost:8000` (or `http://localhost/mayush` if using XAMPP/WAMP).*

---

## 🏗️ Project Structure Highlights

*   `resources/views/frontend/metro/`: The core premium frontend theme.
*   `resources/views/seller/`: The B-Dash (Cyan Data-Dense) vendor dashboard.
*   `public/assets/css/mayush-design-tokens.css`: The source of truth for all global design variables.
*   `docs/`: Contains technical and design documentation.

---

## 🤝 Contributing

When contributing to this repository, please ensure all UI/UX changes align with the `mayush-design-tokens.css`. 

1. Create a new branch (`feature/your-feature-name` or `bugfix/issue-description`).
2. Commit your changes with descriptive, conventional commit messages.
3. Push your branch and open a Pull Request.
4. Ensure no hardcoded fonts or raw hex colors are used in new views — **always use CSS variables**.

---

<div align="center">
  <p>Built with ❤️ for the future of Moroccan Interior Design.</p>
</div>
