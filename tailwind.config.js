/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.jsx",
  ],
  theme: {
    extend: {
        colors: {
            premium: {
                gold: '#D4AF37',
                dark: '#1a1a1a',
            }
        }
    },
  },
  plugins: [],
}
