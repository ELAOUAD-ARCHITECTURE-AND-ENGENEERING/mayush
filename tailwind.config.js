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
            },
            mayush: {
                orange: '#D97434',
                'orange-hover': '#C46524',
                'soft-orange': 'rgba(217, 116, 52, 0.10)',
                black: '#1A1A1A',
                'soft-black': 'rgba(26, 26, 26, 0.06)',
                beige: '#F5F1E8',
                'beige-alt': '#F0E8DD',
                white: '#FFFFFF',
                gray: '#8B8B8B',
                border: '#E5E0D8',
                text: '#333333',
                'text-muted': '#666666',
                'text-light': '#999999',
                success: '#00A86B',
                danger: '#E53935',
                warning: '#F3AF3D',
                info: '#1565C0',
            },
        },
        fontFamily: {
            heading: ["'Playfair Display'", "'Cormorant Garamond'", 'Georgia', 'serif'],
            body: ["'Inter'", "'Poppins'", "'Montserrat'", 'system-ui', 'sans-serif'],
        },
        borderRadius: {
            mayush: {
                sm: '4px',
                md: '6px',
                lg: '8px',
                xl: '12px',
                '2xl': '16px',
            },
        },
        boxShadow: {
            mayush: {
                card: '0 2px 8px rgba(0, 0, 0, 0.08)',
                'card-hover': '0 8px 24px rgba(0, 0, 0, 0.12)',
                cta: '0 8px 16px rgba(217, 116, 52, 0.30)',
                'cta-hover': '0 12px 24px rgba(217, 116, 52, 0.40)',
                modal: '0 16px 48px rgba(0, 0, 0, 0.16)',
                dropdown: '0 4px 12px rgba(0, 0, 0, 0.10)',
            },
        },
        maxWidth: {
            mayush: '1200px',
        },
    },
  },
  plugins: [],
}
